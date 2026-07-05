<?php

namespace App\GameEngines;

use App\Contracts\GameEngine;
use App\DTO\GameOutcome;
use App\Services\ProvablyFairService;

final class SlotsEngine implements GameEngine
{
    /** @var array<int, string> */
    private const SYMBOLS = [
        'cherry', 'cherry', 'cherry', 'cherry', 'cherry', 'cherry', 'cherry',
        'lemon', 'lemon', 'lemon', 'lemon', 'lemon',
        'bell', 'bell', 'bell', 'bell',
        'star', 'star', 'star',
        'seven',
    ];

    /** @var array<string, int> */
    private const TRIPLE_HUNDREDTHS = [
        'cherry' => 300,
        'lemon' => 400,
        'bell' => 600,
        'star' => 1000,
        'seven' => 2000,
    ];

    private const PAIR_HUNDREDTHS = 125;

    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function code(): string
    {
        return 'slots';
    }

    public function version(): string
    {
        return '2.0.0';
    }

    public function rules(): array
    {
        return [
            'code' => $this->code(),
            'engine_version' => $this->version(),
            'rng' => 'hmac-sha256-uniform-int-v1',
            'reels' => 3,
            'symbol_weights' => ['cherry' => 7, 'lemon' => 5, 'bell' => 4, 'star' => 3, 'seven' => 1],
            'pair_multiplier' => 1.25,
            'triple_multipliers' => ['cherry' => 3, 'lemon' => 4, 'bell' => 6, 'star' => 10, 'seven' => 20],
        ];
    }

    public function theoreticalRtpBasisPoints(): int
    {
        return 9504;
    }

    public function simulationCases(): array
    {
        return [['label' => 'default', 'bet' => []]];
    }

    public function play(int $stake, array $bet, string $serverSeed, string $clientSeed, int $nonce): GameOutcome
    {
        $symbols = [];
        for ($reel = 0; $reel < 3; $reel++) {
            $index = $this->fairness->uniformInt($serverSeed, $clientSeed.':slots:'.$reel, $nonce, 0, count(self::SYMBOLS) - 1);
            $symbols[] = self::SYMBOLS[$index];
        }

        $multiplierHundredths = $this->multiplierHundredths($symbols);
        $payout = intdiv($stake * $multiplierHundredths, 100);

        return new GameOutcome(
            won: $payout > 0,
            payout: $payout,
            result: [
                'symbols' => $symbols,
                'multiplier' => $multiplierHundredths / 100,
                'match' => $this->matchLabel($symbols),
                'paytable_version' => 'v2',
            ],
        );
    }

    /** @param array<int, string> $symbols */
    private function multiplierHundredths(array $symbols): int
    {
        if (count(array_unique($symbols)) === 1) {
            return self::TRIPLE_HUNDREDTHS[$symbols[0]] ?? 0;
        }

        return count(array_unique($symbols)) === 2 ? self::PAIR_HUNDREDTHS : 0;
    }

    /** @param array<int, string> $symbols */
    private function matchLabel(array $symbols): string
    {
        if (count(array_unique($symbols)) === 1) {
            return 'triple';
        }

        return count(array_unique($symbols)) === 2 ? 'pair' : 'none';
    }
}
