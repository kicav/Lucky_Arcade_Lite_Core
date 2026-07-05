<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GrantPromotionalCreditsAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserActionController extends Controller
{
    public function show(User $user): View
    {
        abort_if($user->is_admin, 404);
        return view('admin.users.show', [
            'player' => $user->load('wallet'),
            'entries' => $user->gameEntries()->with('game')->latest()->limit(20)->get(),
            'ledger' => $user->ledgerEntries()->latest()->limit(20)->get(),
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is_admin, 404);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);
        $before = $user->only(['suspended_at', 'suspension_reason']);
        $user->update(['suspended_at' => now(), 'suspension_reason' => $data['reason']]);
        $this->audit($request, $user, 'user.suspended', $before, $user->only(['suspended_at', 'suspension_reason']));
        return back()->with('success', 'Player account suspended.');
    }

    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is_admin, 404);
        $before = $user->only(['suspended_at', 'suspension_reason']);
        $user->update(['suspended_at' => null, 'suspension_reason' => null]);
        $this->audit($request, $user, 'user.unsuspended', $before, ['suspended_at' => null, 'suspension_reason' => null]);
        return back()->with('success', 'Player account reactivated.');
    }

    public function grant(Request $request, User $user, GrantPromotionalCreditsAction $action): RedirectResponse
    {
        abort_if($user->is_admin, 404);
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:10', 'max:100000'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);
        $action->execute($request->user(), $user, $data['amount'], $data['reason'], $request);
        return back()->with('success', number_format($data['amount']).' credits granted.');
    }

    private function audit(Request $request, User $subject, string $action, ?array $before, array $after): void
    {
        AuditLog::query()->create([
            'actor_id' => $request->user()->id, 'action' => $action,
            'subject_type' => User::class, 'subject_id' => $subject->id,
            'before' => $before, 'after' => $after, 'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000), 'created_at' => now(),
        ]);
    }
}
