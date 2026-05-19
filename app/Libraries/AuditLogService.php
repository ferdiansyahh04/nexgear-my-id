<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

/**
 * Centralised audit trail. Call this from any controller that mutates state.
 *
 * Common actions:
 *   product.create / product.update / product.delete
 *   product_image.add / product_image.delete
 *   category.create / category.update / category.delete
 *   order.status_change / order.cancel
 *   coupon.create / coupon.update / coupon.delete
 *   message.status_change
 *   stock.alert_subscribe / stock.alert_dispatched
 */
class AuditLogService
{
    /**
     * Record an audit event. Designed to be best-effort: never throws.
     */
    public function log(string $action, array $details = []): void
    {
        try {
            $model   = new AuditLogModel();
            $request = service('request');
            $userId  = (int) (session('user_id') ?? 0);

            $row = [
                'user_id'     => $userId > 0 ? $userId : null,
                'actor_label' => $details['actor_label'] ?? (session('user_email') ?: 'system'),
                'action'      => $action,
                'target_type' => $details['target_type'] ?? null,
                'target_id'   => isset($details['target_id']) ? (int) $details['target_id'] : null,
                'meta'        => isset($details['meta']) ? json_encode($details['meta'], JSON_UNESCAPED_UNICODE) : null,
                'ip_address'  => $request instanceof \CodeIgniter\HTTP\IncomingRequest ? $request->getIPAddress() : null,
            ];

            $model->insert($row);
        } catch (\Throwable $e) {
            // Audit must never break the user-facing flow.
            log_message('warning', 'Audit log failed: ' . $e->getMessage());
        }
    }
}
