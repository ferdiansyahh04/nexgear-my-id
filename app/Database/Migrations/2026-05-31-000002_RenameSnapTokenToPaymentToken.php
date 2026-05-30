<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Renames cart.snap_token → cart.payment_token.
 *
 * The payment integration switched from Midtrans Snap to Duitku Pop. The
 * column is now gateway-agnostic (it stores the Duitku `reference`). On
 * environments provisioned after the original migration was corrected, the
 * column is already named payment_token — so this guards on the actual schema
 * and is a no-op in that case.
 */
class RenameSnapTokenToPaymentToken extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldNames('cart');

        if (in_array('snap_token', $fields, true) && ! in_array('payment_token', $fields, true)) {
            $this->forge->modifyColumn('cart', [
                'snap_token' => [
                    'name'       => 'payment_token',
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('cart');

        if (in_array('payment_token', $fields, true) && ! in_array('snap_token', $fields, true)) {
            $this->forge->modifyColumn('cart', [
                'payment_token' => [
                    'name'       => 'snap_token',
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
            ]);
        }
    }
}
