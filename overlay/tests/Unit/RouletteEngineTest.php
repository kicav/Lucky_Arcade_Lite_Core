<?php

namespace Tests\Unit;

use App\GameEngines\RouletteEngine;
use App\Services\ProvablyFairService;
use PHPUnit\Framework\TestCase;

class RouletteEngineTest extends TestCase
{
    public function test_result_is_a_valid_european_roulette_number(): void
    {
        $engine = new RouletteEngine(new ProvablyFairService());
        $outcome = $engine->play(
            100,
            ['type' => 'color', 'selection' => 'red'],
            'server',
            'client',
            1,
        );

        $this->assertGreaterThanOrEqual(0, $outcome->result['number']);
        $this->assertLessThanOrEqual(36, $outcome->result['number']);
        $this->assertContains($outcome->result['color'], ['red', 'black', 'green']);
    }
}
