<?php

namespace Tests\Unit;

use App\GameEngines\DiceEngine;
use App\Services\ProvablyFairService;
use PHPUnit\Framework\TestCase;

class DiceEngineTest extends TestCase
{
    public function test_same_seeds_and_nonce_produce_same_result(): void
    {
        $engine = new DiceEngine(new ProvablyFairService());
        $bet = ['direction' => 'under', 'target' => 50];

        $a = $engine->play(100, $bet, 'server', 'client', 8);
        $b = $engine->play(100, $bet, 'server', 'client', 8);

        $this->assertSame($a->result, $b->result);
        $this->assertSame($a->payout, $b->payout);
    }
}
