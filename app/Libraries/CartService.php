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
     * Get the raw cart count (sum of all quantities).
     */
    public function count(): int
    {
        return array_sum(session('cart') ?? []);
    }
}
