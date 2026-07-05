<?php

namespace App\DTO;

final readonly class GameOutcome
{
    public function __construct(
        public bool $won,
        public int $payout,
        public array $result,
    ) {
    }
}
