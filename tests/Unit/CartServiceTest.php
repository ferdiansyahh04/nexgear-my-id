<?php

namespace Tests\Unit;

use App\Libraries\CartService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for CartService.
 */
class CartServiceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear cart before each test
        session()->remove('cart');
    }

    public function testEmptyCartReturnsEmptyArray(): void
    {
        $service = new CartService();
        $this->assertSame([], $service->items());
    }

    public function testEmptyCartTotalIsZero(): void
    {
        $service = new CartService();
        $this->assertSame(0.0, $service->total());
    }

    public function testEmptyCartCountIsZero(): void
    {
        $service = new CartService();
        $this->assertSame(0, $service->count());
    }

    public function testCountReflectsSessionCart(): void
    {
        session()->set('cart', [1 => 3, 2 => 5]);
        $service = new CartService();
        $this->assertSame(8, $service->count());
    }

    public function testTotalAcceptsPrecomputedItems(): void
    {
        $service = new CartService();
        $items = [
            ['product' => ['id' => 1], 'qty' => 2, 'subtotal' => 500000.0],
            ['product' => ['id' => 2], 'qty' => 1, 'subtotal' => 300000.0],
        ];
        $this->assertEqualsWithDelta(800000.0, $service->total($items), 0.01);
    }
}
