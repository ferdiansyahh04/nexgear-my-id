<?php

namespace App\Controllers;

use App\Models\ProductModel;

class HomeController extends BaseController
{
    public function index()
    {
        $products = $this->curatedFeatured(6);

        return view('home', [
            'title'    => 'NexGear Store',
            'products' => $products,
        ]);
    }

    /**
     * Build the home "Curated Store" lineup.
     *
     * Instead of just the newest rows (which made the grid show whatever was
     * imported last — e.g. six deskmats in a row), we surface a premium, varied
     * "best of" selection: the flagship (most expensive, in-stock) product from
     * each category for breadth, then fill any remaining slots with the next
     * most premium products overall. Flagships are the halo products, so this
     * doubles as a "best of" showcase across keyboards, mice, IEMs & mousepads.
     *
     * The catalogue is small, so we curate in PHP from a single query rather
     * than firing one query per category.
     *
     * @return list<array<string, mixed>>
     */
    private function curatedFeatured(int $limit): array
    {
        $all = (new ProductModel())
            ->where('stock >', 0)
            ->orderBy('price', 'DESC')
            ->findAll();

        if ($all === []) {
            return [];
        }

        // First pass — one flagship per category (list is already price-desc,
        // so the first product seen for each category is its most premium one).
        $picked    = [];
        $pickedIds = [];
        $seenCats  = [];
        foreach ($all as $product) {
            $catId = (int) ($product['category_id'] ?? 0);
            if (isset($seenCats[$catId])) {
                continue;
            }
            $seenCats[$catId]      = true;
            $picked[]              = $product;
            $pickedIds[(int) $product['id']] = true;
            if (count($picked) >= $limit) {
                break;
            }
        }

        // Second pass — fill remaining slots with the next most premium
        // products that weren't already picked.
        if (count($picked) < $limit) {
            foreach ($all as $product) {
                if (count($picked) >= $limit) {
                    break;
                }
                if (isset($pickedIds[(int) $product['id']])) {
                    continue;
                }
                $picked[]                        = $product;
                $pickedIds[(int) $product['id']] = true;
            }
        }

        // Present the lineup most-premium first for a strong opening row.
        usort($picked, static fn ($a, $b) => (int) $b['price'] <=> (int) $a['price']);

        return $picked;
    }
}
