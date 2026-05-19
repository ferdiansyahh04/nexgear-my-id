<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'product_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'rating' => ['type' => 'TINYINT', 'unsigned' => true, 'null' => false],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'body' => ['type' => 'TEXT', 'null' => true],
            'verified_purchase' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id', 'product_id'], 'uniq_review_user_product');
        $this->forge->addKey('product_id', false, false, 'idx_reviews_product');
        $this->forge->addForeignKey('user_id',    'users',    'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reviews', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('reviews', true);
    }
}
