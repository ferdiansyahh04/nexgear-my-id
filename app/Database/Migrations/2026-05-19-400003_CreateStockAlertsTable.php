<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockAlertsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => false],
            'product_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'notified_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['email', 'product_id'], 'uniq_stock_alerts_email_product');
        $this->forge->addKey(['product_id', 'notified_at'], false, false, 'idx_stock_alerts_product_notify');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_alerts', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('stock_alerts', true);
    }
}
