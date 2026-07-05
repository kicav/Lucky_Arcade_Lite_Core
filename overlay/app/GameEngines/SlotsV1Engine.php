<?php

namespace App\GameEngines;

use App\Contracts\GameEngine;
use App\DTO\GameOutcome;
use App\Services\ProvablyFairService;

final class SlotsV1Engine implements GameEngine
{
    /** @var array<int, string> */
    private const SYMBOLS = [
        'cherry', 'cherry', 'cherry', 'cherry', 'cherry', 'cherry', 'cherry',
        'lemon', 'lemon', 'lemon', 'lemon', 'lemon',
        'bell', 'bell', 'bell', 'bell',
        'star', 'star', 'star',
        'seven',
    ];

    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function code(): string
    {
        return 'slots';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function rules(): array
    {
        return [
            'code' => $this->code(),
            'engine_version' => $this->version(),
            'rng' => 'hmac-sha256-uniform-int-v1',
            'reels' => 3,
            'symbol_weights' => ['cherry' => 7, 'lemon' => 5, 'bell' => 4, 'star' => 3, 'seven' => 1],
            'pair_multiplier' => 1.5,
            'triple_multipliers' => ['cherry' => 5, 'lemon' => 4, 'bell' => 8, 'star' => 12, 'seven' => 25],
        ];
    }

    public function theoreticalRtpBasisPoints(): int
    {
        return 11945;
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

        $multiplierTenths = $this->multiplierTenths($symbols);
        $payout = intdiv($stake * $multiplierTenths, 10);

        return new GameOutcome(
            won: $payout > 0,
            payout: $payout,
            result: ['symbols' => $symbols, 'multiplier' => $multiplierTenths / 10, 'match' => $this->matchLabel($symbols)],
        );
    }

    /** @param array<int, string> $symbols */
    private function multiplierTenths(array $symbols): int
    {
        if (count(array_unique($symbols)) === 1) {
            return match ($symbols[0]) { 'seven' => 250, 'star' => 120, 'bell' => 80, 'cherry' => 50, 'lemon' => 40, default => 0 };
        }

        return count(array_unique($symbols)) === 2 ? 15 : 0;
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
