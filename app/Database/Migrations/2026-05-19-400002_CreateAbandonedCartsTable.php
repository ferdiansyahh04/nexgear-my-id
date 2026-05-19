<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbandonedCartsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'items_json'       => ['type' => 'TEXT', 'null' => false],
            'total'            => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => false, 'default' => 0],
            'item_count'       => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'last_activity_at' => ['type' => 'DATETIME', 'null' => false],
            'reminded_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('user_id', 'uniq_abandoned_carts_user');
        $this->forge->addKey(['last_activity_at', 'reminded_at'], false, false, 'idx_abandoned_carts_activity');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('abandoned_carts', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('abandoned_carts', true);
    }
}
