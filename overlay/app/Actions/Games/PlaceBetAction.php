<?php

namespace App\Actions\Games;

use App\Enums\LedgerDirection;
use App\Models\FairnessSeed;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\LedgerEntry;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FairnessSeedService;
use App\Services\GameRulesetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PlaceBetAction
{
    public function __construct(
        private readonly GameRulesetService $rulesets,
        private readonly FairnessSeedService $seeds,
    ) {}

    /** @param array<string, mixed> $bet */
    public function execute(User $user, Game $game, int $stake, array $bet, string $requestId): GameEntry
    {
        return DB::transaction(function () use ($user, $game, $stake, $bet, $requestId): GameEntry {
            $existing = GameEntry::query()->where('user_id', $user->id)->where('request_id', $requestId)->first();
            if ($existing) return $existing;

            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->validatePlayer($lockedUser, $stake);

            $lockedGame = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();
            $this->validateGame($lockedGame, $stake);
            $ruleset = $this->rulesets->activeFor($lockedGame);
            $engine = $this->rulesets->engine($ruleset);

            $wallet = Wallet::query()->where('user_id', $lockedUser->id)->lockForUpdate()->firstOrFail();
            if ($wallet->balance < $stake) throw ValidationException::withMessages(['stake' => 'Insufficient virtual credits.']);

            $seed = FairnessSeed::query()->where('user_id', $lockedUser->id)->where('active', true)->lockForUpdate()->first();
            if (! $seed) {
                $created = $this->seeds->create($lockedUser);
                $seed = FairnessSeed::query()->whereKey($created->id)->lockForUpdate()->firstOrFail();
            }

            $outcome = $engine->play($stake, $bet, $seed->server_seed, $seed->client_seed, $seed->nonce);
            $entry = GameEntry::query()->create([
                'user_id' => $lockedUser->id,
                'game_id' => $lockedGame->id,
                'game_ruleset_id' => $ruleset->id,
                'engine_version' => $ruleset->engine_version,
                'rules_snapshot' => $ruleset->rules,
                'rules_checksum' => $ruleset->checksum,
                'fairness_seed_id' => $seed->id,
                'stake' => $stake,
                'payout' => $outcome->payout,
                'net' => $outcome->payout - $stake,
                'bet' => $bet,
                'result' => $outcome->result + ['won' => $outcome->won],
                'client_seed' => $seed->client_seed,
                'nonce' => $seed->nonce,
                'server_seed_hash' => $seed->server_seed_hash,
                'request_id' => $requestId,
                'status' => 'settled',
            ]);

            $wallet->decrement('balance', $stake);
            $wallet->refresh();
            LedgerEntry::query()->create([
                'user_id' => $lockedUser->id, 'wallet_id' => $wallet->id,
                'direction' => LedgerDirection::Debit, 'amount' => $stake,
                'balance_after' => $wallet->balance, 'type' => 'game_stake',
                'idempotency_key' => "{$lockedUser->id}:{$requestId}:stake",
                'reference_type' => GameEntry::class, 'reference_id' => $entry->id,
                'metadata' => ['game' => $lockedGame->code, 'engine_version' => $ruleset->engine_version],
            ]);

            if ($outcome->payout > 0) {
                $wallet->increment('balance', $outcome->payout);
                $wallet->refresh();
                LedgerEntry::query()->create([
                    'user_id' => $lockedUser->id, 'wallet_id' => $wallet->id,
                    'direction' => LedgerDirection::Credit, 'amount' => $outcome->payout,
                    'balance_after' => $wallet->balance, 'type' => 'game_payout',
                    'idempotency_key' => "{$lockedUser->id}:{$requestId}:payout",
                    'reference_type' => GameEntry::class, 'reference_id' => $entry->id,
                    'metadata' => ['game' => $lockedGame->code, 'engine_version' => $ruleset->engine_version],
                ]);
            }

            $seed->increment('nonce');
            return $entry->fresh(['game', 'ruleset']);
        }, attempts: 3);
    }

    private function validatePlayer(User $user, int $stake): void
    {
        if ($user->isSuspended()) throw ValidationException::withMessages(['account' => 'This account is suspended.']);
        if ($user->isSelfExcluded()) throw ValidationException::withMessages(['account' => 'Self-exclusion is currently active.']);
        if ($user->daily_stake_limit !== null) {
            $used = (int) GameEntry::query()->where('user_id', $user->id)->whereDate('created_at', today())->sum('stake');
            if ($used + $stake > $user->daily_stake_limit) {
                throw ValidationException::withMessages(['stake' => 'Daily stake limit reached.']);
            }
        }
    }

    private function validateGame(Game $game, int $stake): void
    {
        if (! in_array($game->code, Game::LITE_CODES, true) || ! $game->enabled) {
            throw ValidationException::withMessages(['game' => 'This game is unavailable.']);
        }
        if ($stake < $game->min_bet || $stake > $game->max_bet) {
            throw ValidationException::withMessages(['stake' => "Stake must be between {$game->min_bet} and {$game->max_bet} credits."]);
        }
    }
}
