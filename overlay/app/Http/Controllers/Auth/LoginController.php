<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, SecurityEventService $events): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', mb_strtolower($credentials['email']))->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $events->record($user, 'login.failed', $request, ['email' => mb_strtolower($credentials['email'])]);
            throw ValidationException::withMessages([
                'email' => 'Email or password is incorrect.',
            ]);
        }

        if (! $user->is_admin && $user->isSuspended()) {
            $events->record($user, 'login.blocked_suspended', $request);
            throw ValidationException::withMessages([
                'email' => 'This account is suspended. Contact an administrator for assistance.',
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->regenerate();
            $request->session()->put([
                'two_factor_login_user_id' => $user->id,
                'two_factor_login_remember' => $request->boolean('remember'),
            ]);
            $events->record($user, 'login.password_verified', $request);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $events->record($user, 'login.success', $request, ['method' => 'password']);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, SecurityEventService $events): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $events->record($user, 'logout', $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
