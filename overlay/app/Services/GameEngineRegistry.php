<?php

namespace App\Services;

use App\Contracts\GameEngine;
use App\GameEngines\CoinFlipEngine;
use App\GameEngines\DiceEngine;
use App\GameEngines\RouletteEngine;
use App\GameEngines\SlotsEngine;
use App\GameEngines\SlotsV1Engine;
use InvalidArgumentException;

final class GameEngineRegistry
{
    /** @var array<string, array<string, GameEngine>> */
    private array $engines = [];

    /** @var array<string, string> */
    private array $currentVersions = [
        'dice' => '1.0.0',
        'roulette' => '1.0.0',
        'coinflip' => '1.0.0',
        'slots' => '2.0.0',
    ];

    public function __construct(
        DiceEngine $dice,
        RouletteEngine $roulette,
        CoinFlipEngine $coinFlip,
        SlotsV1Engine $slotsV1,
        SlotsEngine $slotsV2,
    ) {
        foreach ([$dice, $roulette, $coinFlip, $slotsV1, $slotsV2] as $engine) {
            $this->engines[$engine->code()][$engine->version()] = $engine;
        }
    }

    public function for(string $code, ?string $version = null): GameEngine
    {
        $resolved = $version ?? $this->currentVersions[$code] ?? null;
        $engine = $resolved === null ? null : ($this->engines[$code][$resolved] ?? null);
        if (! $engine) throw new InvalidArgumentException("Unsupported game engine: {$code}@".($resolved ?? 'current'));
        return $engine;
    }

    public function currentFor(string $code): GameEngine { return $this->for($code); }

    /** @return array<int, GameEngine> */
    public function versionsFor(string $code): array
    {
        $versions = array_values($this->engines[$code] ?? []);
        usort($versions, fn (GameEngine $a, GameEngine $b): int => version_compare($a->version(), $b->version()));
        return $versions;
    }
}
