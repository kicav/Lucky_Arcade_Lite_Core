<?php

namespace Tests\Unit;

use App\GameEngines\SlotsEngine;
use App\Services\ProvablyFairService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SlotsEngineTest extends TestCase
{
    #[Test]
    public function it_is_deterministic_and_returns_three_known_symbols(): void
    {
        $engine = new SlotsEngine(new ProvablyFairService());
        $first = $engine->play(100, ['lines' => 1], 'server', 'client', 12);
        $second = $engine->play(100, ['lines' => 1], 'server', 'client', 12);

        $this->assertSame($first->result, $second->result);
        $this->assertSame($first->payout, $second->payout);
        $this->assertCount(3, $first->result['symbols']);
        foreach ($first->result['symbols'] as $symbol) {
            $this->assertContains($symbol, ['cherry', 'lemon', 'bell', 'star', 'seven']);
        }
        $this->assertGreaterThanOrEqual(0, $first->payout);
    }
}
