<?php

namespace App\Libraries;

use App\Models\ProductModel;

/**
 * CartService — Centralised cart logic.
 *
 * Extracted from CartController so that both controllers
 * and views can access cart data without tight coupling.
 */
class CartService
{
    /**
     * Get enriched cart items with product data and subtotals.
     *
     * @return array<int, array{product: array, qty: int, subtotal: float}>
     */
    public function items(): array
    {
        $cart = session('cart') ?? [];
        if ($cart === []) {
            return [];
        }

        $products = (new ProductModel())->whereIn('id', array_keys($cart))->findAll();
        $items = [];

        foreach ($products as $product) {
            $qty = (int) ($cart[$product['id']] ?? 0);
            if ($qty < 1) {
                continue;
            }

            $items[] = [
                'product'  => $product,
                'qty'      => $qty,
                'subtotal' => $qty * (float) $product['price'],
            ];
        }

        return $items;
    }

    /**
     * Calculate grand total from cart items.
     */
    public function total(?array $items = null): float
    {
        $items ??= $this->items();

        return array_sum(array_column($items, 'subtotal'));
    }

    /**
     * Subtotal alias for clarity when juxtaposed with discount/total.
     */
    public function subtotal(?array $items = null): float
    {
        return $this->total($items);
    }

    /**
     * Final amount after applying any session-bound coupon.
     */
    public function finalTotal(?array $items = null): float
    {
        $subtotal = $this->subtotal($items);
        $discount = (new \App\Libraries\CouponService())->currentDiscount($subtotal);
        return max(0, $subtotal - $discount);
    }

    /**
     * Discount currently applied (0 if no coupon).
     */
    public function discount(?array $items = null): float
    {
        $subtotal = $this->subtotal($items);
        return (new \App\Libraries\CouponService())->currentDiscount($subtotal);
    }

    /**
     * Get the raw cart count (sum of all quantities).
     */
    public function count(): int
    {
        return array_sum(session('cart') ?? []);
    }
}
