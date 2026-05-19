<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add composite indexes that support the dashboard, reports, and account
 * order-history hot paths.
 *
 *   - cart(status, created_at)   — every report query filters by both
 *   - cart(user_id, created_at)  — /account/orders pagination + ORDER BY date
 */
class AddCartReportingIndexes extends Migration
{
    public function up()
    {
        // 1. Composite index for status+date filtering (Dashboard, Reports)
        if (! $this->indexExists('cart', 'idx_cart_status_created')) {
            $this->db->query('ALTER TABLE cart ADD INDEX idx_cart_status_created (status, created_at)');
        }

        // 2. Better account-history index. Add the new one FIRST so the FK
        //    on `user_id` always has a backing index, then drop the old one.
        if (! $this->indexExists('cart', 'idx_cart_user_status_created')) {
            $this->db->query('ALTER TABLE cart ADD INDEX idx_cart_user_status_created (user_id, status, created_at)');
        }
        if ($this->indexExists('cart', 'idx_cart_user_status')) {
            $this->db->query('ALTER TABLE cart DROP INDEX idx_cart_user_status');
        }
    }

    public function down()
    {
        if ($this->indexExists('cart', 'idx_cart_status_created')) {
            $this->db->query('ALTER TABLE cart DROP INDEX idx_cart_status_created');
        }
        if ($this->indexExists('cart', 'idx_cart_user_status_created')) {
            $this->db->query('ALTER TABLE cart DROP INDEX idx_cart_user_status_created');
        }
        if (! $this->indexExists('cart', 'idx_cart_user_status')) {
            $this->db->query('ALTER TABLE cart ADD INDEX idx_cart_user_status (user_id, status)');
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        $row = $this->db->query(
            'SELECT 1 AS hit
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$table, $name]
        )->getRowArray();
        return ! empty($row['hit']);
    }
}
