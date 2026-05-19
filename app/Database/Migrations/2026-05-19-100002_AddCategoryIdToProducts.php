<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryIdToProducts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'category_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
                'after' => 'description',
            ],
        ]);
        $this->db->query('ALTER TABLE products ADD INDEX idx_products_category (category_id)');
        $this->db->query('
            ALTER TABLE products
            ADD CONSTRAINT fk_products_category
            FOREIGN KEY (category_id) REFERENCES categories(id)
            ON UPDATE CASCADE ON DELETE SET NULL
        ');

        // Best-effort backfill from product names.
        $cats = [];
        foreach ($this->db->table('categories')->get()->getResultArray() as $row) {
            $cats[strtolower($row['slug'])] = (int) $row['id'];
        }

        $rules = [
            'keyboards'   => ['keyboard'],
            'mice'        => ['mouse'],
            'headsets'    => ['headset', 'headphone'],
            'mousepads'   => ['mousepad', 'pad', 'mat'],
            'microphones' => ['mic'],
            'controllers' => ['controller', 'dock'],
        ];

        foreach ($this->db->table('products')->get()->getResultArray() as $product) {
            $name = strtolower((string) $product['name']);
            foreach ($rules as $slug => $needles) {
                foreach ($needles as $needle) {
                    if (str_contains($name, $needle) && isset($cats[$slug])) {
                        $this->db->table('products')
                            ->where('id', $product['id'])
                            ->update(['category_id' => $cats[$slug]]);
                        continue 3;
                    }
                }
            }
        }
    }

    public function down()
    {
        $this->db->query('ALTER TABLE products DROP FOREIGN KEY fk_products_category');
        $this->db->query('ALTER TABLE products DROP INDEX idx_products_category');
        $this->forge->dropColumn('products', 'category_id');
    }
}
