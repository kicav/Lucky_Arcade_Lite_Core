<?php

namespace Tests\Unit;

use App\GameEngines\CoinFlipEngine;
use App\Services\ProvablyFairService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CoinFlipEngineTest extends TestCase
{
    #[Test]
    public function it_is_deterministic_and_pays_1_98x(): void
    {
        $engine = new CoinFlipEngine(new ProvablyFairService());
        $first = $engine->play(100, ['selection' => 'heads'], 'server', 'client', 7);
        $second = $engine->play(100, ['selection' => 'heads'], 'server', 'client', 7);

        $this->assertSame($first->result, $second->result);
        $this->assertContains($first->result['side'], ['heads', 'tails']);
        $this->assertContains($first->payout, [0, 198]);
    }

    #[Test]
    public function it_rejects_an_invalid_side(): void
    {
        $this->expectException(ValidationException::class);
        (new CoinFlipEngine(new ProvablyFairService()))
            ->play(100, ['selection' => 'edge'], 'server', 'client', 0);
    }
}
