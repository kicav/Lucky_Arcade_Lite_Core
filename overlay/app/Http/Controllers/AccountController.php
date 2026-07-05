<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\SecurityEventService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('account.show', [
            'user' => $user,
            'todayStake' => (int) $user->gameEntries()->whereDate('created_at', today())->sum('stake'),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $before = ['name' => $user->name];
        $user->update($data);
        $this->audit($request, 'account.profile.updated', $before, ['name' => $user->name]);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request, SecurityEventService $events): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);
        $this->audit($request, 'account.password.updated', null, ['password_changed' => true]);
        $events->record($request->user(), 'password.changed', $request);

        return back()->with('success', 'Password updated.');
    }

    public function updatePlayControls(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'daily_stake_limit' => ['nullable', 'integer', 'min:10', 'max:1000000'],
            'self_exclusion_days' => ['nullable', 'integer', 'in:1,7,30'],
        ]);

        $user = $request->user();
        $before = $user->only(['daily_stake_limit', 'self_excluded_until']);
        $user->daily_stake_limit = $data['daily_stake_limit'] ?? null;

        if (! empty($data['self_exclusion_days'])) {
            $candidate = now()->addDays((int) $data['self_exclusion_days']);
            if ($user->self_excluded_until === null || $candidate->isAfter($user->self_excluded_until)) {
                $user->self_excluded_until = $candidate;
            }
        }

        $user->save();
        $this->audit(
            $request,
            'account.play_controls.updated',
            $before,
            $user->only(['daily_stake_limit', 'self_excluded_until']),
        );

        return back()->with('success', 'Play controls updated. Active self-exclusion cannot be cancelled early.');
    }

    private function audit(Request $request, string $action, ?array $before, array $after): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => $request->user()::class,
            'subject_id' => $request->user()->id,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
