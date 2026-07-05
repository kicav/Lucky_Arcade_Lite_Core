<?php

namespace App\Actions\Admin;

use App\Enums\LedgerDirection;
use App\Models\AuditLog;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GrantPromotionalCreditsAction
{
    public function execute(User $actor, User $recipient, int $amount, string $reason, Request $request): void
    {
        DB::transaction(function () use ($actor, $recipient, $amount, $reason, $request): void {
            $wallet = Wallet::query()->where('user_id', $recipient->id)->lockForUpdate()->firstOrFail();
            $before = $wallet->balance;
            $wallet->increment('balance', $amount);
            $wallet->refresh();

            LedgerEntry::query()->create([
                'user_id' => $recipient->id, 'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Credit, 'amount' => $amount,
                'balance_after' => $wallet->balance, 'type' => 'admin_credit',
                'idempotency_key' => 'admin-credit:'.Str::uuid(),
                'reference_type' => User::class, 'reference_id' => $actor->id,
                'metadata' => ['reason' => $reason, 'actor_id' => $actor->id],
            ]);

            AuditLog::query()->create([
                'actor_id' => $actor->id, 'action' => 'user.credits.granted',
                'subject_type' => User::class, 'subject_id' => $recipient->id,
                'before' => ['balance' => $before],
                'after' => ['balance' => $wallet->balance, 'amount' => $amount, 'reason' => $reason],
                'ip_address' => $request->ip(), 'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'created_at' => now(),
            ]);
        }, attempts: 3);
    }
}
