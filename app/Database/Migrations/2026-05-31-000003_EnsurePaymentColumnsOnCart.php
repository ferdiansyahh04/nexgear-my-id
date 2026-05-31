<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Self-healing guarantee that every payment column exists on `cart`.
 *
 * The earlier AddPaymentColumnsToCart / RenameSnapTokenToPaymentToken pair did
 * not apply cleanly on production (the deploy swallowed a migration failure),
 * leaving the table without payment_ref/payment_status/etc. — which surfaced
 * as "Unknown column 'payment_ref'" when starting a payment.
 *
 * This migration inspects the live schema and adds only the columns that are
 * missing, so it converges the table to the correct shape from ANY prior state
 * (fresh, partially-migrated, or pre-rename snap_token). It is safe to re-run.
 */
class EnsurePaymentColumnsOnCart extends Migration
{
    public function up()
    {
        $cols = $this->db->getFieldNames('cart');

        $add = [];

        if (! in_array('payment_status', $cols, true)) {
            $add['payment_status'] = [
                'type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'unpaid',
            ];
        }
        if (! in_array('payment_ref', $cols, true)) {
            $add['payment_ref'] = ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true];
        }
        // Token column: only add payment_token when NEITHER it nor the legacy
        // snap_token exists (the rename migration handles snap_token → payment_token).
        if (! in_array('payment_token', $cols, true) && ! in_array('snap_token', $cols, true)) {
            $add['payment_token'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true];
        }
        if (! in_array('payment_method', $cols, true)) {
            $add['payment_method'] = ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true];
        }
        if (! in_array('paid_at', $cols, true)) {
            $add['paid_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if ($add !== []) {
            $this->forge->addColumn('cart', $add);
        }

        // Ensure the lookup index on payment_ref exists (ignore if already there).
        try {
            $indexes = $this->db->getIndexData('cart');
            $hasIndex = false;
            foreach ($indexes as $idx) {
                if ($idx->name === 'idx_cart_payment_ref') {
                    $hasIndex = true;
                    break;
                }
            }
            if (! $hasIndex) {
                $this->db->query('CREATE INDEX idx_cart_payment_ref ON cart (payment_ref)');
            }
        } catch (\Throwable $e) {
            // Index creation is a nice-to-have; never let it block the schema fix.
            log_message('warning', 'Could not ensure idx_cart_payment_ref: {m}', ['m' => $e->getMessage()]);
        }
    }

    public function down()
    {
        // Intentionally a no-op: this is a convergence/repair migration, and the
        // companion AddPaymentColumnsToCart::down() already drops the columns.
    }
}
