<?php

namespace App\GameEngines;

use App\Contracts\GameEngine;
use App\DTO\GameOutcome;
use App\Services\ProvablyFairService;
use Illuminate\Validation\ValidationException;

final class CoinFlipEngine implements GameEngine
{
    private const MULTIPLIER = 1.98;

    public function __construct(private readonly ProvablyFairService $fairness)
    {
    }

    public function code(): string
    {
        return 'coinflip';
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
            'sides' => ['heads', 'tails'],
            'multiplier' => self::MULTIPLIER,
        ];
    }

    public function theoreticalRtpBasisPoints(): int
    {
        return 9900;
    }

    public function simulationCases(): array
    {
        return [
            ['label' => 'heads', 'bet' => ['selection' => 'heads']],
            ['label' => 'tails', 'bet' => ['selection' => 'tails']],
        ];
    }

    public function play(
        int $stake,
        array $bet,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
    ): GameOutcome {
        $selection = (string) ($bet['selection'] ?? '');

        if (! in_array($selection, ['heads', 'tails'], true)) {
            throw ValidationException::withMessages(['selection' => 'Choose heads or tails.']);
        }

        $value = $this->fairness->uniformInt($serverSeed, $clientSeed.':coinflip', $nonce, 0, 1);
        $side = $value === 0 ? 'heads' : 'tails';
        $won = $selection === $side;

        return new GameOutcome(
            won: $won,
            payout: $won ? (int) floor($stake * self::MULTIPLIER) : 0,
            result: ['side' => $side, 'selection' => $selection, 'raw_value' => $value, 'multiplier' => self::MULTIPLIER],
        );
    }
}
