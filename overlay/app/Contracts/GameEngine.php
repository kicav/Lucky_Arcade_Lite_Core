<?php

namespace App\Contracts;

use App\DTO\GameOutcome;

interface GameEngine
{
    public function code(): string;

    public function version(): string;

    /** @return array<string, mixed> */
    public function rules(): array;

    public function theoreticalRtpBasisPoints(): int;

    /**
     * @return array<int, array{label: string, bet: array<string, mixed>}>
     */
    public function simulationCases(): array;

    /**
     * @param array<string, mixed> $bet
     */
    public function play(
        int $stake,
        array $bet,
        string $serverSeed,
        string $clientSeed,
        int $nonce,
    ): GameOutcome;
}
