<?php

namespace Tests\Unit;

use App\Libraries\MidtransService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Midtrans as MidtransConfig;

/**
 * DB-free tests for the security-critical parts of the Midtrans integration:
 * webhook signature verification and transaction-status mapping.
 */
class MidtransServiceTest extends CIUnitTestCase
{
    private function service(): MidtransService
    {
        $cfg            = new MidtransConfig();
        $cfg->serverKey = 'SB-Mid-server-UNITTEST';
        $cfg->clientKey = 'SB-Mid-client-UNITTEST';
        $cfg->production = false;

        return new MidtransService($cfg);
    }

    public function testIsEnabledReflectsKeys(): void
    {
        $this->assertTrue($this->service()->isEnabled());

        $empty = new MidtransConfig();
        $this->assertFalse((new MidtransService($empty))->isEnabled());
    }

    public function testSandboxEndpoints(): void
    {
        $svc = $this->service();
        $this->assertStringContainsString('sandbox', $svc->snapJsUrl());
    }

    public function testVerifySignatureAcceptsValid(): void
    {
        $svc        = $this->service();
        $orderId    = 'NEXGEAR-7-1700000000';
        $statusCode = '200';
        $gross      = '329000';
        $sig        = hash('sha512', $orderId . $statusCode . $gross . 'SB-Mid-server-UNITTEST');

        $this->assertTrue($svc->verifySignature([
            'order_id'      => $orderId,
            'status_code'   => $statusCode,
            'gross_amount'  => $gross,
            'signature_key' => $sig,
        ]));
    }

    public function testVerifySignatureRejectsTampered(): void
    {
        $svc = $this->service();

        $this->assertFalse($svc->verifySignature([
            'order_id'      => 'NEXGEAR-7-1700000000',
            'status_code'   => '200',
            'gross_amount'  => '329000',
            'signature_key' => 'not-a-valid-signature',
        ]));
    }

    public function testVerifySignatureRejectsMissingFields(): void
    {
        $this->assertFalse($this->service()->verifySignature([]));
    }

    public function testMapStatusCoversLifecycle(): void
    {
        $svc = $this->service();

        $this->assertSame('paid',     $svc->mapStatus(['transaction_status' => 'settlement']));
        $this->assertSame('paid',     $svc->mapStatus(['transaction_status' => 'capture', 'fraud_status' => 'accept']));
        $this->assertSame('pending',  $svc->mapStatus(['transaction_status' => 'capture', 'fraud_status' => 'challenge']));
        $this->assertSame('pending',  $svc->mapStatus(['transaction_status' => 'pending']));
        $this->assertSame('failed',   $svc->mapStatus(['transaction_status' => 'deny']));
        $this->assertSame('failed',   $svc->mapStatus(['transaction_status' => 'cancel']));
        $this->assertSame('expired',  $svc->mapStatus(['transaction_status' => 'expire']));
        $this->assertSame('refunded', $svc->mapStatus(['transaction_status' => 'refund']));
        $this->assertSame('unpaid',   $svc->mapStatus(['transaction_status' => 'something_else']));
    }
}
