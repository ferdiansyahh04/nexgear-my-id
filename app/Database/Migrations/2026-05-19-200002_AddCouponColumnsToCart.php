<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCouponColumnsToCart extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cart', [
            'coupon_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'after'      => 'total',
            ],
            'discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0,
                'after'      => 'coupon_code',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cart', 'coupon_code');
        $this->forge->dropColumn('cart', 'discount');
    }
}
