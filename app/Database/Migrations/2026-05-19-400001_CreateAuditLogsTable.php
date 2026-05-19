<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'actor_label' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'action'      => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'target_type' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'target_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'meta'        => ['type' => 'TEXT', 'null' => true],
            'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('action', false, false, 'idx_audit_action');
        $this->forge->addKey(['target_type', 'target_id'], false, false, 'idx_audit_target');
        $this->forge->addKey('created_at', false, false, 'idx_audit_created');
        $this->forge->createTable('audit_logs', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs', true);
    }
}
