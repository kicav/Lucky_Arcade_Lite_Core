<?php

namespace App\Services;

use App\Contracts\GameEngine;
use App\Models\Game;
use App\Models\GameRuleset;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class GameRulesetService
{
    public function __construct(
        private readonly GameEngineRegistry $engines,
        private readonly CanonicalJsonService $canonical,
    ) {}

    public function activeFor(Game $game): GameRuleset
    {
        $game->loadMissing('activeRuleset');
        if ($game->activeRuleset) {
            $this->assertCompatible($game->activeRuleset);
            return $game->activeRuleset;
        }

        return DB::transaction(function () use ($game): GameRuleset {
            $locked = Game::query()->whereKey($game->id)->lockForUpdate()->firstOrFail();
            $locked->load('activeRuleset');
            if ($locked->activeRuleset) return $locked->activeRuleset;
            $engine = $this->engines->currentFor($locked->code);
            $ruleset = $this->registerEngine($locked, $engine, 'active');
            $locked->forceFill(['active_ruleset_id' => $ruleset->id])->save();
            return $ruleset;
        }, attempts: 3);
    }

    public function engine(GameRuleset $ruleset): GameEngine
    {
        $this->assertCompatible($ruleset);
        return $this->engines->for($ruleset->game->code, $ruleset->engine_version);
    }

    public function syncAll(): void
    {
        Game::query()->whereIn('code', Game::LITE_CODES)->orderBy('id')->each(function (Game $game): void {
            $current = $this->engines->currentFor($game->code);
            $currentRuleset = null;
            foreach ($this->engines->versionsFor($game->code) as $engine) {
                $status = $engine->version() === $current->version() ? 'active' : 'retired';
                $ruleset = $this->registerEngine($game, $engine, $status);
                if ($status === 'active') $currentRuleset = $ruleset;
            }
            if ($currentRuleset) $game->forceFill(['active_ruleset_id' => $currentRuleset->id])->save();
        });
    }

    public function assertCompatible(GameRuleset $ruleset): void
    {
        $ruleset->loadMissing('game');
        $engine = $this->engines->for($ruleset->game->code, $ruleset->engine_version);
        $engineChecksum = $this->canonical->checksum($engine->rules());
        $storedChecksum = $this->canonical->checksum($ruleset->rules ?? []);
        if (! hash_equals($ruleset->checksum, $storedChecksum) || ! hash_equals($ruleset->checksum, $engineChecksum)) {
            throw new RuntimeException("Ruleset checksum mismatch for {$ruleset->game->code}@{$ruleset->engine_version}.");
        }
    }

    private function registerEngine(Game $game, GameEngine $engine, string $status): GameRuleset
    {
        $rules = $engine->rules();
        $checksum = $this->canonical->checksum($rules);
        $existing = GameRuleset::query()->where('game_id', $game->id)->where('engine_version', $engine->version())->first();
        if ($existing) {
            if (! hash_equals($existing->checksum, $checksum)) {
                throw new RuntimeException("Published rules changed for {$game->code}@{$engine->version()}.");
            }
            $existing->forceFill(['status' => $status, 'activated_at' => $status === 'active' ? now() : $existing->activated_at, 'retired_at' => $status === 'retired' ? now() : null])->save();
            return $existing;
        }

        return GameRuleset::query()->create([
            'game_id' => $game->id,
            'engine_version' => $engine->version(),
            'status' => $status,
            'rules' => $rules,
            'checksum' => $checksum,
            'theoretical_rtp_bp' => $engine->theoreticalRtpBasisPoints(),
            'activated_at' => $status === 'active' ? now() : null,
            'retired_at' => $status === 'retired' ? now() : null,
        ]);
    }
}
