<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\GameEntry;
use App\Services\FairnessVerificationService;
use Illuminate\Console\Command;

class VerifyGameEntries extends Command
{
    protected $signature = 'arcade:verify-entries {--limit=500 : Maximum entries to inspect}';

    protected $description = 'Recompute revealed game entries and report fairness or ruleset mismatches';

    public function handle(FairnessVerificationService $verification): int
    {
        $limit = max(1, min(10000, (int) $this->option('limit')));
        $entries = GameEntry::query()
            ->with(['game', 'ruleset', 'fairnessSeed'])
            ->whereHas('game', fn ($query) => $query->whereIn('code', Game::LITE_CODES))
            ->whereHas('fairnessSeed', fn ($query) => $query->whereNotNull('revealed_server_seed'))
            ->latest('id')
            ->limit($limit)
            ->get();

        $mismatches = 0;
        foreach ($entries as $entry) {
            $result = $verification->verify($entry);
            if (! ($result['verified'] ?? false)) {
                $mismatches++;
                $this->error("Entry #{$entry->id} {$entry->game->code}@".($entry->engine_version ?: 'legacy').' failed verification.');
            }
        }

        $this->line("Checked {$entries->count()} revealed entries; {$mismatches} mismatch(es).");

        return $mismatches === 0 ? self::SUCCESS : self::FAILURE;
    }
}
