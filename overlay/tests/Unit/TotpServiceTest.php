<?php

namespace Tests\Unit;

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    public function test_it_matches_a_known_totp_value(): void
    {
        $service = new TotpService();

        $this->assertSame('996554', $service->currentCode('JBSWY3DPEHPK3PXP', 59));
        $this->assertTrue($service->verify('JBSWY3DPEHPK3PXP', '996554', 0, 59));
        $this->assertFalse($service->verify('JBSWY3DPEHPK3PXP', '000000', 0, 59));
    }

    public function test_generated_recovery_codes_are_unique_and_hashed(): void
    {
        $recovery = (new TotpService())->generateRecoveryCodes();

        $this->assertCount(8, $recovery['plain']);
        $this->assertCount(8, array_unique($recovery['plain']));
        $this->assertTrue(password_verify($recovery['plain'][0], $recovery['hashed'][0]));
    }
}
