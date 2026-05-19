<?php

namespace App\Libraries;

use App\Models\ProductModel;

/**
 * Tracks the last few products a visitor opened and surfaces them as a
 * server-rendered strip. State lives in the PHP session so it survives
 * across requests but doesn't require an account.
 */
class RecentlyViewedService
{
    public const MAX_ITEMS = 6;
    private const SESSION_KEY = 'recently_viewed';

    /**
     * Push a product id onto the front of the queue, dedup, cap at MAX_ITEMS.
     */
    public function track(int $productId): void
    {
        if ($productId < 1) {
            return;
        }

        $list = $this->ids();
        $list = array_values(array_filter($list, static fn ($id) => (int) $id !== $productId));
        array_unshift($list, $productId);
        $list = array_slice($list, 0, self::MAX_ITEMS);

        session()->set(self::SESSION_KEY, $list);
    }

    /**
     * Hydrated product rows in last-seen order.
     *
     * @param int|null $excludeId Optional product id to omit (e.g. the page
     *                            currently being viewed).
     */
    public function items(?int $excludeId = null): array
    {
        $ids = $this->ids();
        if ($excludeId !== null) {
            $ids = array_values(array_filter($ids, static fn ($id) => (int) $id !== $excludeId));
        }
        if ($ids === []) {
            return [];
        }

        $rows  = (new ProductModel())->whereIn('id', $ids)->findAll();
        $byId  = [];
        foreach ($rows as $row) {
            $byId[(int) $row['id']] = $row;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[(int) $id])) {
                $ordered[] = $byId[(int) $id];
            }
        }

        return $ordered;
    }

    /**
     * @return int[]
     */
    public function ids(): array
    {
        $list = session(self::SESSION_KEY) ?? [];
        return array_values(array_map('intval', is_array($list) ? $list : []));
    }

    public function clear(): void
    {
        session()->remove(self::SESSION_KEY);
    }
}
