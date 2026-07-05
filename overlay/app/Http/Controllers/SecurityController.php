<?php

namespace App\Http\Controllers;

use App\Services\SecurityEventService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function show(Request $request, TotpService $totp): View
    {
        $user = $request->user();
        $pendingSecret = $request->session()->get('two_factor_setup_secret');

        return view('security.show', [
            'user' => $user,
            'events' => $user->securityEvents()->latest('created_at')->limit(50)->get(),
            'pendingSecret' => $pendingSecret,
            'otpAuthUri' => $pendingSecret ? $totp->otpAuthUri($user, $pendingSecret) : null,
            'recoveryCodes' => $request->session()->get('recovery_codes', []),
        ]);
    }

    public function begin(Request $request, TotpService $totp, SecurityEventService $events): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        abort_if($request->user()->hasTwoFactorEnabled(), 422, 'Two-factor authentication is already enabled.');

        $request->session()->put('two_factor_setup_secret', $totp->generateSecret());
        $events->record($request->user(), 'two_factor.setup_started', $request);

        return back()->with('success', 'Authenticator setup started. Add the secret to your authenticator app, then confirm a code.');
    }

    public function confirm(Request $request, TotpService $totp, SecurityEventService $events): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'size:6']]);
        $secret = (string) $request->session()->get('two_factor_setup_secret');

        if ($secret === '' || ! $totp->verify($secret, $data['code'])) {
            return back()->withErrors(['code' => 'The authenticator code is invalid or expired.']);
        }

        $recovery = $totp->generateRecoveryCodes();
        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recovery['hashed'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->flash('recovery_codes', $recovery['plain']);
        $events->record($request->user(), 'two_factor.enabled', $request);

        return back()->with('success', 'Two-factor authentication enabled. Save the recovery codes now; they will not be shown again.');
    }

    public function disable(Request $request, TotpService $totp, SecurityEventService $events): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'max:32'],
        ]);

        $user = $request->user();
        if (! $user->hasTwoFactorEnabled()) {
            return back()->withErrors(['code' => 'Two-factor authentication is not enabled.']);
        }

        $valid = $totp->verify((string) $user->two_factor_secret, $data['code'])
            || $totp->consumeRecoveryCode($user, $data['code']);

        if (! $valid) {
            return back()->withErrors(['code' => 'The authenticator or recovery code is invalid.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $events->record($user, 'two_factor.disabled', $request);

        return back()->with('success', 'Two-factor authentication disabled.');
    }

    public function regenerateRecoveryCodes(Request $request, TotpService $totp, SecurityEventService $events): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        if (! $user->hasTwoFactorEnabled() || ! $totp->verify((string) $user->two_factor_secret, $data['code'])) {
            return back()->withErrors(['code' => 'The authenticator code is invalid.']);
        }

        $recovery = $totp->generateRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $recovery['hashed']])->save();
        $request->session()->flash('recovery_codes', $recovery['plain']);
        $events->record($user, 'two_factor.recovery_codes_regenerated', $request);

        return back()->with('success', 'New recovery codes generated. Previous recovery codes no longer work.');
    }
}
