<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNewsletterSubscribersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => false],
            'confirmed'     => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'token'         => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'unsubscribed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email', 'uniq_newsletter_email');
        $this->forge->createTable('newsletter_subscribers', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('newsletter_subscribers', true);
    }
}
