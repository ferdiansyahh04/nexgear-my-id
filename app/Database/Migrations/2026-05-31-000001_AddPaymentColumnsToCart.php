<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds payment-tracking columns to the cart/order table for the Midtrans Snap
 * integration. The order lifecycle status (checked_out → paid → …) already
 * exists; these columns track the gateway-side payment state alongside it.
 */
class AddPaymentColumnsToCart extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cart', [
            // Gateway payment state, independent of fulfilment status.
            'payment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'unpaid', // unpaid | pending | paid | failed | expired | refunded
                'after'      => 'discount',
            ],
            // The unique order_id sent to Midtrans (e.g. NEXGEAR-12-20260531...).
            'payment_ref' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'payment_status',
            ],
            // The most recent Snap token (so a payment page can be reopened).
            'snap_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'payment_ref',
            ],
            // The channel the customer paid with (bank_transfer, gopay, qris…).
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'snap_token',
            ],
            'paid_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'payment_method',
            ],
        ]);

        // Webhook looks orders up by payment_ref — index it.
        $this->forge->addKey('payment_ref', false, false, 'idx_cart_payment_ref');
        $this->db->query('CREATE INDEX idx_cart_payment_ref ON cart (payment_ref)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_cart_payment_ref ON cart');
        $this->forge->dropColumn('cart', ['payment_status', 'payment_ref', 'snap_token', 'payment_method', 'paid_at']);
    }
}
