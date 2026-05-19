<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSearchLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'query'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'count'      => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 1],
            'last_seen_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('query', 'uniq_search_logs_query');
        $this->forge->addKey(['count', 'last_seen_at'], false, false, 'idx_search_logs_count_seen');
        $this->forge->createTable('search_logs', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('search_logs', true);
    }
}
