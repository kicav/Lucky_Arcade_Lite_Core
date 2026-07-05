<?php

namespace Tests\Unit;

use App\Services\ProvablyFairService;
use PHPUnit\Framework\TestCase;

class ProvablyFairServiceTest extends TestCase
{
    public function test_digest_is_deterministic(): void
    {
        $service = new ProvablyFairService();

        $a = $service->digest('server', 'client', 42);
        $b = $service->digest('server', 'client', 42);

        $this->assertSame($a, $b);
        $this->assertSame(64, strlen($a));
    }

    public function test_uniform_integer_stays_in_range(): void
    {
        $service = new ProvablyFairService();

        for ($nonce = 0; $nonce < 1000; $nonce++) {
            $value = $service->uniformInt('server-seed', 'client-seed', $nonce, 0, 36);
            $this->assertGreaterThanOrEqual(0, $value);
            $this->assertLessThanOrEqual(36, $value);
        }
    }
}
