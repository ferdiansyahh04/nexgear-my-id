<?php

namespace App\Libraries;

use App\Models\ProductModel;
use App\Models\StockAlertModel;
use App\Models\UserModel;

/**
 * Stock-alert service. Lets visitors subscribe to "notify me when X is back
 * in stock" and lets admins / cron dispatch those notifications when the
 * product transitions from out-of-stock to in-stock.
 */
class StockAlertService
{
    /**
     * Subscribe an email + product. Idempotent — re-subscribing simply
     * resets `notified_at` so the user is reminded again on next restock.
     *
     * @return array{ok: bool, message: string}
     */
    public function subscribe(string $email, int $productId, ?int $userId = null): array
    {
        $email = strtolower(trim($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Please provide a valid email.'];
        }

        $product = (new ProductModel())->find($productId);
        if (! $product) {
            return ['ok' => false, 'message' => 'Product not found.'];
        }

        $model = new StockAlertModel();
        $existing = $model
            ->where('email', $email)
            ->where('product_id', $productId)
            ->first();

        $payload = [
            'email'       => $email,
            'product_id'  => $productId,
            'user_id'     => $userId,
            'notified_at' => null,
        ];

        if ($existing) {
            $model->update($existing['id'], $payload);
            return ['ok' => true, 'message' => "We'll email you the moment it's back."];
        }

        $model->insert($payload);
        return ['ok' => true, 'message' => "We'll email you the moment it's back."];
    }

    /**
     * Dispatch all pending alerts for a single product (i.e. notified_at IS
     * NULL). Called when stock transitions from 0 → positive.
     *
     * @return int number of alerts marked as dispatched
     */
    public function dispatchFor(int $productId): int
    {
        $model = new StockAlertModel();
        $pending = $model
            ->where('product_id', $productId)
            ->where('notified_at IS NULL', null, false)
            ->find();

        if ($pending === []) return 0;

        $product = (new ProductModel())->find($productId);
        $audit   = new AuditLogService();
        $now     = date('Y-m-d H:i:s');

        foreach ($pending as $row) {
            $model->update($row['id'], ['notified_at' => $now]);

            (new MailerService())->send(
                $row['email'],
                'Back in stock: ' . ($product['name'] ?? 'NexGear product'),
                'emails/stock_alert',
                ['product' => $product]
            );

            $audit->log('stock.alert_dispatched', [
                'actor_label' => 'system',
                'target_type' => 'product',
                'target_id'   => $productId,
                'meta'        => [
                    'email'   => $row['email'],
                    'product' => $product['name'] ?? null,
                ],
            ]);
        }

        return count($pending);
    }

    /**
     * Sweep ALL products that are now in stock and have pending alerts.
     * Useful as a safety-net cron in case the in-controller hook missed
     * a transition (e.g. stock changed via direct SQL).
     */
    public function sweepAll(): int
    {
        $candidates = db_connect()
            ->table('stock_alerts AS sa')
            ->select('DISTINCT sa.product_id')
            ->join('products AS p', 'p.id = sa.product_id')
            ->where('sa.notified_at IS NULL', null, false)
            ->where('p.stock >', 0)
            ->get()
            ->getResultArray();

        $total = 0;
        foreach ($candidates as $row) {
            $total += $this->dispatchFor((int) $row['product_id']);
        }
        return $total;
    }
}
