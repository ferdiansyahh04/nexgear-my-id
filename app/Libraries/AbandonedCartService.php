<?php

namespace App\Libraries;

use App\Models\AbandonedCartModel;
use App\Models\ProductModel;
use App\Models\UserModel;

/**
 * Abandoned cart tracker.
 *
 * The session cart already lives on the client. To recover when sessions
 * expire / the user closes the browser, we snapshot the cart for logged-in
 * users into the `abandoned_carts` table on every cart mutation. A
 * scheduled `php spark cart:remind-abandoned` command then dispatches
 * reminders for snapshots older than the threshold.
 */
class AbandonedCartService
{
    public const REMIND_AFTER_HOURS = 1;

    /**
     * Persist the current session cart for a logged-in user. Called from
     * CartController on add / update / remove.
     */
    public function snapshot(): void
    {
        $userId = (int) (session('user_id') ?? 0);
        if ($userId < 1) return;

        $cart = session('cart') ?? [];
        $model = new AbandonedCartModel();

        if ($cart === []) {
            $model->where('user_id', $userId)->delete();
            return;
        }

        $items = [];
        $total = 0.0;
        $count = 0;
        $products = (new ProductModel())->whereIn('id', array_keys($cart))->findAll();
        foreach ($products as $p) {
            $qty = (int) ($cart[$p['id']] ?? 0);
            if ($qty < 1) continue;
            $items[] = [
                'id'    => (int) $p['id'],
                'name'  => $p['name'],
                'qty'   => $qty,
                'price' => (float) $p['price'],
            ];
            $total += $qty * (float) $p['price'];
            $count += $qty;
        }

        $existing = $model->where('user_id', $userId)->first();
        $row = [
            'user_id'          => $userId,
            'items_json'       => json_encode($items, JSON_UNESCAPED_UNICODE),
            'total'            => $total,
            'item_count'       => $count,
            'last_activity_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $row['reminded_at'] = null;
            $model->update($existing['id'], $row);
        } else {
            $model->insert($row);
        }
    }

    public function clearForUser(int $userId): void
    {
        if ($userId < 1) return;
        (new AbandonedCartModel())->where('user_id', $userId)->delete();
    }

    /**
     * Find snapshots that are stale and not yet reminded.
     *
     * @return array<int, array>
     */
    public function pendingReminders(int $hours = self::REMIND_AFTER_HOURS): array
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        return (new AbandonedCartModel())
            ->where('last_activity_at <', $cutoff)
            ->where('reminded_at IS NULL', null, false)
            ->orderBy('last_activity_at', 'ASC')
            ->find();
    }

    /**
     * Stamp the snapshot as reminded and return the user's email (or null).
     * Production: hook this up to a mailer. Development: stamp only.
     */
    public function markReminded(int $snapshotId): ?string
    {
        $model = new AbandonedCartModel();
        $row = $model->find($snapshotId);
        if (! $row) return null;

        $user  = (new UserModel())->find((int) $row['user_id']);
        $email = $user['email'] ?? null;

        $model->update($snapshotId, ['reminded_at' => date('Y-m-d H:i:s')]);
        return $email;
    }
}
