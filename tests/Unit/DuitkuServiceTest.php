<?php

namespace Tests\Unit;

use App\Libraries\DuitkuService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Duitku as DuitkuConfig;

/**
 * DB-free tests for the security-critical parts of the Duitku integration:
 * callback signature verification and result-code mapping.
 */
class DuitkuServiceTest extends CIUnitTestCase
{
    private function service(): DuitkuService
    {
        $cfg               = new DuitkuConfig();
        $cfg->merchantCode = 'DS0001';
        $cfg->apiKey       = 'UNIT-TEST-API-KEY';
        $cfg->production   = false;

        return new DuitkuService($cfg);
    }

    public function testIsEnabledReflectsKeys(): void
    {
        $this->assertTrue($this->service()->isEnabled());
        $this->assertFalse((new DuitkuService(new DuitkuConfig()))->isEnabled());
    }

    public function testSandboxEndpoint(): void
    {
        $this->assertStringContainsString('sandbox', $this->service()->popJsUrl());
    }

    public function testVerifyCallbackAcceptsValid(): void
    {
        $svc      = $this->service();
        $amount   = '329000';
        $orderId  = 'NEXGEAR-7-1700000000';
        $sig      = hash_hmac('sha256', 'DS0001' . $amount . $orderId, 'UNIT-TEST-API-KEY');

        $this->assertTrue($svc->verifyCallback([
            'merchantCode'    => 'DS0001',
            'amount'          => $amount,
            'merchantOrderId' => $orderId,
            'signature'       => $sig,
        ]));
    }

    public function testVerifyCallbackRejectsTampered(): void
    {
        $this->assertFalse($this->service()->verifyCallback([
            'merchantCode'    => 'DS0001',
            'amount'          => '329000',
            'merchantOrderId' => 'NEXGEAR-7-1700000000',
            'signature'       => 'not-a-valid-signature',
        ]));
    }

    public function testVerifyCallbackRejectsForeignMerchant(): void
    {
        $svc     = $this->service();
        $amount  = '1000';
        $orderId = 'X-1';
        // A signature correctly computed for a DIFFERENT merchant code.
        $sig = hash_hmac('sha256', 'DOTHER' . $amount . $orderId, 'UNIT-TEST-API-KEY');

        $this->assertFalse($svc->verifyCallback([
            'merchantCode'    => 'DOTHER',
            'amount'          => $amount,
            'merchantOrderId' => $orderId,
            'signature'       => $sig,
        ]));
    }

    public function testVerifyCallbackRejectsMissingFields(): void
    {
        $this->assertFalse($this->service()->verifyCallback([]));
    }

    public function testMapResultCode(): void
    {
        $svc = $this->service();
        $this->assertSame('paid',   $svc->mapResultCode('00'));
        $this->assertSame('failed', $svc->mapResultCode('01'));
        $this->assertSame('failed', $svc->mapResultCode('02'));
        $this->assertSame('unpaid', $svc->mapResultCode('99'));
    }
}
