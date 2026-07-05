<?php

namespace App\Services;

use App\Models\GameEntry;

final class FairnessVerificationService
{
    public function __construct(
        private readonly ProvablyFairService $fairness,
        private readonly GameEngineRegistry $engines,
        private readonly CanonicalJsonService $canonical,
    ) {
    }

    /** @return array<string, mixed> */
    public function verify(GameEntry $entry): array
    {
        $entry->loadMissing(['game', 'fairnessSeed', 'ruleset']);
        $seed = $entry->fairnessSeed;

        if (! $seed || ! $seed->revealed_server_seed) {
            return [
                'verified' => false,
                'reason' => 'The server seed is still hidden. Rotate the active seed first.',
            ];
        }

        $version = $entry->engine_version ?: ($entry->ruleset?->engine_version ?: '1.0.0');
        $engine = $this->engines->for($entry->game->code, $version);
        $snapshot = $entry->rules_snapshot ?: ($entry->ruleset?->rules ?: $engine->rules());
        $storedChecksum = $entry->rules_checksum ?: ($entry->ruleset?->checksum ?: $this->canonical->checksum($snapshot));

        $hashMatches = hash_equals(
            $entry->server_seed_hash,
            $this->fairness->hashServerSeed($seed->revealed_server_seed),
        );
        $snapshotChecksumMatches = hash_equals($storedChecksum, $this->canonical->checksum($snapshot));
        $engineRulesMatch = hash_equals($storedChecksum, $this->canonical->checksum($engine->rules()));

        $recomputed = $engine->play(
            stake: $entry->stake,
            bet: $entry->bet,
            serverSeed: $seed->revealed_server_seed,
            clientSeed: $entry->client_seed,
            nonce: $entry->nonce,
        );

        $resultMatches = $recomputed->result == array_diff_key($entry->result, ['won' => true]);
        $winMatches = $recomputed->won === (bool) ($entry->result['won'] ?? false);
        $payoutMatches = $recomputed->payout === $entry->payout;

        return [
            'verified' => $hashMatches && $snapshotChecksumMatches && $engineRulesMatch && $resultMatches && $winMatches && $payoutMatches,
            'engine_version' => $version,
            'rules_checksum' => $storedChecksum,
            'hash_matches' => $hashMatches,
            'rules_snapshot_matches' => $snapshotChecksumMatches,
            'engine_rules_match' => $engineRulesMatch,
            'result_matches' => $resultMatches,
            'win_matches' => $winMatches,
            'payout_matches' => $payoutMatches,
            'stored_result' => $entry->result,
            'recomputed_result' => $recomputed->result + ['won' => $recomputed->won],
            'stored_payout' => $entry->payout,
            'recomputed_payout' => $recomputed->payout,
        ];
    }
}
