<?php

namespace App\Libraries;

use App\Models\ProductModel;
use App\Models\WishlistModel;

/**
 * WishlistService — encapsulates persistence for logged-in users plus a
 * session-bound buffer for guests. On login, guest items can be merged
 * via mergeGuestIntoUser().
 */
class WishlistService
{
    private const GUEST_KEY = 'guest_wishlist';

    /**
     * Toggle a product. Returns the new state ('added' | 'removed' | 'invalid').
     */
    public function toggle(int $productId): string
    {
        if (! (new ProductModel())->find($productId)) {
            return 'invalid';
        }

        $userId = (int) (session('user_id') ?? 0);

        if ($userId > 0) {
            $model    = new WishlistModel();
            $existing = $model->where(['user_id' => $userId, 'product_id' => $productId])->first();

            if ($existing) {
                $model->delete($existing['id']);
                return 'removed';
            }

            $model->insert(['user_id' => $userId, 'product_id' => $productId]);
            return 'added';
        }

        // Guest path — session list of ids
        $list = $this->guestList();
        if (in_array($productId, $list, true)) {
            $list = array_values(array_filter($list, static fn ($id) => $id !== $productId));
            session()->set(self::GUEST_KEY, $list);
            return 'removed';
        }

        $list[] = $productId;
        session()->set(self::GUEST_KEY, array_values(array_unique($list)));
        return 'added';
    }

    public function has(int $productId): bool
    {
        $userId = (int) (session('user_id') ?? 0);
        if ($userId > 0) {
            return (bool) (new WishlistModel())
                ->where(['user_id' => $userId, 'product_id' => $productId])
                ->countAllResults();
        }
        return in_array($productId, $this->guestList(), true);
    }

    /**
     * @return int[] product ids in user's wishlist
     */
    public function ids(): array
    {
        $userId = (int) (session('user_id') ?? 0);
        if ($userId > 0) {
            $rows = (new WishlistModel())
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll();
            return array_map(static fn ($r) => (int) $r['product_id'], $rows);
        }
        return $this->guestList();
    }

    public function count(): int
    {
        return count($this->ids());
    }

    /**
     * @return array<int, array> hydrated product rows
     */
    public function items(): array
    {
        $ids = $this->ids();
        if ($ids === []) return [];

        $rows = (new ProductModel())->whereIn('id', $ids)->findAll();
        $byId = [];
        foreach ($rows as $row) $byId[(int) $row['id']] = $row;

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) $ordered[] = $byId[$id];
        }
        return $ordered;
    }

    /**
     * Called from AuthController after successful login: lift any guest
     * picks into the user's persistent wishlist.
     */
    public function mergeGuestIntoUser(int $userId): void
    {
        $list = $this->guestList();
        if ($list === [] || $userId < 1) return;

        $validIds = array_map(
            static fn ($row) => (int) $row['id'],
            (new ProductModel())->select('id')->whereIn('id', $list)->findAll()
        );
        if ($validIds === []) {
            session()->remove(self::GUEST_KEY);
            return;
        }

        $model = new WishlistModel();
        foreach ($validIds as $productId) {
            $exists = $model->where(['user_id' => $userId, 'product_id' => $productId])->countAllResults();
            if (! $exists) {
                $model->insert(['user_id' => $userId, 'product_id' => $productId]);
            }
        }
        session()->remove(self::GUEST_KEY);
    }

    /**
     * @return int[]
     */
    private function guestList(): array
    {
        $raw = session(self::GUEST_KEY) ?? [];
        return array_values(array_map('intval', is_array($raw) ? $raw : []));
    }
}
