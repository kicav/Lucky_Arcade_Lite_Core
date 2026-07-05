<?php

namespace App\GameEngines;

use App\Contracts\GameEngine;
use App\DTO\GameOutcome;
use App\Services\ProvablyFairService;
use Illuminate\Validation\ValidationException;

final class DiceEngine implements GameEngine
{
    private const HOUSE_FACTOR = 0.99;

    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function code(): string
    {
        return 'dice';
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
            'house_factor' => self::HOUSE_FACTOR,
            'target_min' => 2,
            'target_max' => 98,
            'roll_min' => 0,
            'roll_max' => 9999,
        ];
    }

    public function theoreticalRtpBasisPoints(): int
    {
        return 9900;
    }

    public function simulationCases(): array
    {
        return [
            ['label' => 'under-50', 'bet' => ['direction' => 'under', 'target' => 50]],
            ['label' => 'over-50', 'bet' => ['direction' => 'over', 'target' => 50]],
            ['label' => 'under-25', 'bet' => ['direction' => 'under', 'target' => 25]],
            ['label' => 'over-75', 'bet' => ['direction' => 'over', 'target' => 75]],
        ];
    }

    public function play(
        int $stake,
        array $bet,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
    ): GameOutcome {
        $direction = (string) ($bet['direction'] ?? '');
        $target = (int) ($bet['target'] ?? 0);

        if (! in_array($direction, ['under', 'over'], true) || $target < 2 || $target > 98) {
            throw ValidationException::withMessages(['bet' => 'Dice bet is invalid.']);
        }

        $raw = $this->fairness->uniformInt($serverSeed, $clientSeed.':dice', $nonce, 0, 9999);
        $threshold = $target * 100;
        $won = $direction === 'under' ? $raw < $threshold : $raw >= $threshold;
        $probability = $direction === 'under' ? $target / 100 : (100 - $target) / 100;
        $multiplier = floor((self::HOUSE_FACTOR / $probability) * 10000) / 10000;
        $payout = $won ? (int) floor($stake * $multiplier) : 0;

        return new GameOutcome(
            won: $won,
            payout: $payout,
            result: [
                'roll' => number_format($raw / 100, 2, '.', ''),
                'raw_roll' => $raw,
                'direction' => $direction,
                'target' => $target,
                'multiplier' => $multiplier,
            ],
        );
    }
}
