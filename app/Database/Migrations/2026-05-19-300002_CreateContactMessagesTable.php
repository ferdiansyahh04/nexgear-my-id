<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => false],
            'subject'    => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'message'    => ['type' => 'TEXT', 'null' => false],
            'status'     => ['type' => "ENUM('new','read','archived')", 'null' => false, 'default' => 'new'],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('status', false, false, 'idx_contact_messages_status');
        $this->forge->createTable('contact_messages', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('contact_messages', true);
    }
}
