<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug', 'uniq_categories_slug');
        $this->forge->createTable('categories', true, ['ENGINE' => 'InnoDB']);

        // Seed a small base set so the storefront has filters from day one.
        $now = date('Y-m-d H:i:s');
        $this->db->table('categories')->insertBatch([
            ['name' => 'Keyboards',  'slug' => 'keyboards',  'description' => 'Mechanical & low-profile decks.',  'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mice',       'slug' => 'mice',       'description' => 'Wireless precision and ergo.',     'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Headsets',   'slug' => 'headsets',   'description' => 'Surround audio for gaming.',       'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mousepads',  'slug' => 'mousepads',  'description' => 'Surfaces with lighting.',          'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Microphones','slug' => 'microphones','description' => 'Streaming-grade vocal capture.',   'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Controllers','slug' => 'controllers','description' => 'Pads and docking stations.',       'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('categories', true);
    }
}
