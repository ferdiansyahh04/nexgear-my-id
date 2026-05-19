<?php

namespace Tests\Unit;

use App\Libraries\CouponService;
use App\Models\CouponModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Unit tests for the coupon validation engine.
 *
 * Uses the real DB (test group) and inserts/cleans a few coupon rows so we
 * don't depend on whatever seed data happens to be present.
 */
class CouponServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;

    private function makeCoupon(array $overrides = []): array
    {
        $base = [
            'code'       => 'TEST_' . bin2hex(random_bytes(3)),
            'type'       => 'percent',
            'value'      => 10,
            'min_total'  => 0,
            'max_uses'   => null,
            'used'       => 0,
            'expires_at' => null,
        ];
        $row = array_merge($base, $overrides);
        $id  = (new CouponModel())->insert($row, true);
        $row['id'] = $id;
        return $row;
    }

    private function dropCoupon(string $code): void
    {
        (new CouponModel())->where('code', $code)->delete();
    }

    protected function setUp(): void
    {
        parent::setUp();
        session()->remove('coupon_code');
    }

    public function testUnknownCodeIsRejected(): void
    {
        $service = new CouponService();
        $result  = $service->validate('NOPE_NOT_A_REAL_CODE_XYZ', 100000);
        $this->assertFalse($result['valid']);
    }

    public function testPercentDiscountAppliesCorrectly(): void
    {
        $row = $this->makeCoupon(['type' => 'percent', 'value' => 15]);
        $service = new CouponService();
        $result  = $service->validate($row['code'], 200000);

        $this->assertTrue($result['valid']);
        $this->assertEqualsWithDelta(30000, $result['discount'], 0.01);
        $this->dropCoupon($row['code']);
    }

    public function testFixedDiscountIsClampedToSubtotal(): void
    {
        $row = $this->makeCoupon(['type' => 'fixed', 'value' => 999999]);
        $service = new CouponService();
        $result  = $service->validate($row['code'], 50000);

        $this->assertTrue($result['valid']);
        $this->assertEqualsWithDelta(50000, $result['discount'], 0.01);
        $this->dropCoupon($row['code']);
    }

    public function testMinTotalEnforcement(): void
    {
        $row = $this->makeCoupon(['type' => 'fixed', 'value' => 25000, 'min_total' => 500000]);
        $service = new CouponService();

        $rejected = $service->validate($row['code'], 100000);
        $this->assertFalse($rejected['valid']);

        $accepted = $service->validate($row['code'], 600000);
        $this->assertTrue($accepted['valid']);

        $this->dropCoupon($row['code']);
    }

    public function testMaxUsesEnforcement(): void
    {
        $row = $this->makeCoupon(['type' => 'percent', 'value' => 5, 'max_uses' => 1, 'used' => 1]);
        $service = new CouponService();
        $result  = $service->validate($row['code'], 100000);

        $this->assertFalse($result['valid']);
        $this->dropCoupon($row['code']);
    }

    public function testExpiredCouponIsRejected(): void
    {
        $row = $this->makeCoupon([
            'type'       => 'percent',
            'value'      => 5,
            'expires_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);
        $service = new CouponService();
        $result  = $service->validate($row['code'], 100000);

        $this->assertFalse($result['valid']);
        $this->dropCoupon($row['code']);
    }

    public function testCurrentDiscountAutoClearsWhenCartShrinks(): void
    {
        $row = $this->makeCoupon(['type' => 'fixed', 'value' => 25000, 'min_total' => 500000]);
        $service = new CouponService();
        $service->apply($row['code'], 600000);

        // Cart shrinks below the minimum
        $this->assertSame(0.0, $service->currentDiscount(100000));
        $this->assertNull($service->applied(), 'Coupon should auto-clear from session');

        $this->dropCoupon($row['code']);
    }
}
