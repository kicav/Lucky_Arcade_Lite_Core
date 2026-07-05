<?php

namespace App\Console\Commands;

use App\Enums\LedgerDirection;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileWallets extends Command
{
    protected $signature = 'wallets:reconcile {--fix : Set wallet balance to the balance reconstructed from its ledger}';

    protected $description = 'Validate wallet balances and every ledger balance_after value.';

    public function handle(): int
    {
        $checked = 0;
        $issues = 0;

        Wallet::query()->with('user:id,email')->orderBy('id')->chunkById(100, function ($wallets) use (&$checked, &$issues): void {
            foreach ($wallets as $wallet) {
                $checked++;
                $entries = $wallet->entries()->orderBy('id')->get();

                if ($entries->isEmpty()) {
                    $this->line("Wallet {$wallet->id} ({$wallet->user?->email}): no ledger entries; current balance {$wallet->balance}.");
                    continue;
                }

                $first = $entries->first();
                $running = $first->direction === LedgerDirection::Debit
                    ? $first->balance_after + $first->amount
                    : $first->balance_after - $first->amount;

                $walletHasIssue = false;

                foreach ($entries as $entry) {
                    $running += $entry->direction === LedgerDirection::Credit
                        ? $entry->amount
                        : -$entry->amount;

                    if ($running !== $entry->balance_after) {
                        $walletHasIssue = true;
                        $this->error("Wallet {$wallet->id}, ledger {$entry->id}: expected balance_after {$running}, stored {$entry->balance_after}.");
                    }
                }

                if ($running !== $wallet->balance) {
                    $walletHasIssue = true;
                    $this->error("Wallet {$wallet->id}: reconstructed {$running}, stored {$wallet->balance}.");

                    if ($this->option('fix')) {
                        DB::transaction(function () use ($wallet, $running): void {
                            $locked = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
                            $locked->update(['balance' => $running]);
                        });
                        $this->warn("Wallet {$wallet->id} balance corrected to {$running}.");
                    }
                }

                if (! $walletHasIssue) {
                    $this->info("Wallet {$wallet->id} ({$wallet->user?->email}) OK: {$running} credits.");
                } else {
                    $issues++;
                }
            }
        });

        $this->newLine();
        $this->line("Checked {$checked} wallet(s); {$issues} wallet(s) with issues.");

        return $issues === 0 ? self::SUCCESS : self::FAILURE;
    }
}
