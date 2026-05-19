<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotpToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'totp_secret' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'password',
            ],
            'totp_enabled' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'totp_secret',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'totp_secret');
        $this->forge->dropColumn('users', 'totp_enabled');
    }
}
