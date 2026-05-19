<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAddressesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'label' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'address' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => false],
            'city' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'postal_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false],
            'is_default' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'is_default'], false, false, 'idx_addresses_user_default');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('addresses', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('addresses', true);
    }
}
