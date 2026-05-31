<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds payment-tracking columns to the cart/order table for the online
 * payment integration. The order lifecycle status (checked_out → paid → …)
 * already exists; these columns track the gateway-side payment state alongside
 * it. (The gateway is Duitku Pop; an earlier revision named the token column
 * snap_token — a follow-up migration renames it to payment_token.)
 */
class AddPaymentColumnsToCart extends Migration
{
    public function up()
    {
        $cols = $this->db->getFieldNames('cart');
        $defs = [
            'payment_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'unpaid', 'after' => 'discount'],
            'payment_ref'    => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'payment_status'],
            'payment_token'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'payment_ref'],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'payment_token'],
            'paid_at'        => ['type' => 'DATETIME', 'null' => true, 'after' => 'payment_method'],
        ];

        // Only add columns that don't already exist (tolerates partial state).
        $add = [];
        foreach ($defs as $name => $def) {
            if (! in_array($name, $cols, true)) {
                $add[$name] = $def;
            }
        }
        if ($add !== []) {
            $this->forge->addColumn('cart', $add);
        }

        // Webhook looks orders up by payment_ref — index it (guarded).
        try {
            $hasIndex = false;
            foreach ($this->db->getIndexData('cart') as $idx) {
                if ($idx->name === 'idx_cart_payment_ref') { $hasIndex = true; break; }
            }
            if (! $hasIndex && in_array('payment_ref', $this->db->getFieldNames('cart'), true)) {
                $this->db->query('CREATE INDEX idx_cart_payment_ref ON cart (payment_ref)');
            }
        } catch (\Throwable $e) {
            log_message('warning', 'idx_cart_payment_ref skipped: {m}', ['m' => $e->getMessage()]);
        }
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_cart_payment_ref ON cart');
        $this->forge->dropColumn('cart', ['payment_status', 'payment_ref', 'payment_token', 'payment_method', 'paid_at']);
    }
}
