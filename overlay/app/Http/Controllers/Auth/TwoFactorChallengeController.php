<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityEventService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_login_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(
        Request $request,
        TotpService $totp,
        SecurityEventService $events,
    ): RedirectResponse {
        $data = $request->validate(['code' => ['required', 'string', 'max:32']]);
        $user = User::query()->find($request->session()->get('two_factor_login_user_id'));

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['two_factor_login_user_id', 'two_factor_login_remember']);
            return redirect()->route('login')->withErrors(['email' => 'The login challenge expired.']);
        }

        $validTotp = $totp->verify((string) $user->two_factor_secret, $data['code']);
        $validRecovery = $validTotp ? false : $totp->consumeRecoveryCode($user, $data['code']);

        if (! $validTotp && ! $validRecovery) {
            $events->record($user, 'login.two_factor_failed', $request);
            return back()->withErrors(['code' => 'The authenticator or recovery code is invalid.']);
        }

        $remember = (bool) $request->session()->pull('two_factor_login_remember', false);
        $request->session()->forget('two_factor_login_user_id');
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $events->record($user, 'login.success', $request, [
            'method' => $validRecovery ? 'recovery_code' : 'totp',
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(['two_factor_login_user_id', 'two_factor_login_remember']);

        return redirect()->route('login');
    }
}
