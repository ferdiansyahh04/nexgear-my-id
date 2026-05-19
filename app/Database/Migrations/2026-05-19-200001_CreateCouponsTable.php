<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => false],
            'type' => ['type' => "ENUM('percent','fixed')", 'null' => false, 'default' => 'percent'],
            'value' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => false, 'default' => 0],
            'min_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => false, 'default' => 0],
            'max_uses' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'used' => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('code', 'uniq_coupons_code');
        $this->forge->createTable('coupons', true, ['ENGINE' => 'InnoDB']);

        // Seed a couple of demo coupons for the project demo
        $now = date('Y-m-d H:i:s');
        $this->db->table('coupons')->insertBatch([
            [
                'code'       => 'WELCOME10',
                'type'       => 'percent',
                'value'      => 10,
                'min_total'  => 0,
                'max_uses'   => null,
                'used'       => 0,
                'expires_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'NEXGEAR50K',
                'type'       => 'fixed',
                'value'      => 50000,
                'min_total'  => 500000,
                'max_uses'   => 100,
                'used'       => 0,
                'expires_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('coupons', true);
    }
}
