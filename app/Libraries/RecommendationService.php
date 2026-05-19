<?php

namespace App\Libraries;

use App\Models\ProductModel;

/**
 * Lightweight content-based recommender.
 *
 * Strategy (in priority order):
 *   1. "Frequently bought together": products that appear in the same orders
 *      as the seed product (mined from cart_items + cart).
 *   2. "Similar in this category": same category, similar price band (±35%),
 *      excluding the seed product itself.
 *   3. Fallback: latest in-stock products.
 */
class RecommendationService
{
    public function forProduct(int $productId, int $limit = 4): array
    {
        $db = db_connect();

        $seed = (new ProductModel())->find($productId);
        if (! $seed) return [];

        // ── Stage 1: frequently bought together ──────────────────
        $coIds = [];
        $coRows = $db->table('cart_items AS ci2')
            ->select('ci2.product_id, COUNT(*) AS hits')
            ->join('cart_items AS ci1', 'ci1.cart_id = ci2.cart_id')
            ->join('cart', 'cart.id = ci1.cart_id')
            ->where('ci1.product_id', $productId)
            ->where('ci2.product_id !=', $productId)
            ->whereIn('cart.status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
            ->groupBy('ci2.product_id')
            ->orderBy('hits', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($coRows as $row) $coIds[] = (int) $row['product_id'];

        $picks = $coIds;

        // ── Stage 2: same category, similar price ────────────────
        if (count($picks) < $limit) {
            $needed = $limit - count($picks);
            $price  = (float) $seed['price'];
            $low    = $price * 0.65;
            $high   = $price * 1.35;

            $similar = (new ProductModel())
                ->where('id !=', $productId);
            if ($picks !== []) $similar->whereNotIn('id', $picks);
            if (! empty($seed['category_id'])) {
                $similar->where('category_id', (int) $seed['category_id']);
            }
            $similarRows = $similar
                ->where('stock >', 0)
                ->where('price >=', $low)
                ->where('price <=', $high)
                ->orderBy('created_at', 'DESC')
                ->limit($needed)
                ->find();

            foreach ($similarRows as $r) $picks[] = (int) $r['id'];
        }

        // ── Stage 3: latest in-stock fallback ────────────────────
        if (count($picks) < $limit) {
            $needed = $limit - count($picks);
            $fallback = (new ProductModel())
                ->where('id !=', $productId);
            if ($picks !== []) $fallback->whereNotIn('id', $picks);

            $fbRows = $fallback
                ->where('stock >', 0)
                ->orderBy('created_at', 'DESC')
                ->limit($needed)
                ->find();

            foreach ($fbRows as $r) $picks[] = (int) $r['id'];
        }

        if ($picks === []) return [];

        // Hydrate, preserving the picks order
        $rows = (new ProductModel())->whereIn('id', $picks)->findAll();
        $byId = [];
        foreach ($rows as $r) $byId[(int) $r['id']] = $r;

        $ordered = [];
        foreach ($picks as $id) {
            if (isset($byId[$id])) $ordered[] = $byId[$id];
            if (count($ordered) >= $limit) break;
        }
        return $ordered;
    }
}
