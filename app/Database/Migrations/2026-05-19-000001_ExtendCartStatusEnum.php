<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds order-lifecycle statuses (paid → processing → shipped → delivered)
 * on top of the existing 'active' / 'checked_out' / 'cancelled' values.
 */
class ExtendCartStatusEnum extends Migration
{
    public function up()
    {
        // ENUM modifications need raw SQL on MySQL.
        $this->db->query("
            ALTER TABLE cart MODIFY COLUMN status
            ENUM('active', 'checked_out', 'paid', 'processing', 'shipped', 'delivered', 'cancelled')
            NOT NULL DEFAULT 'active'
        ");
    }

    public function down()
    {
        // Revert any new statuses to 'checked_out' before narrowing the enum.
        $this->db->query("
            UPDATE cart
            SET status = 'checked_out'
            WHERE status IN ('paid', 'processing', 'shipped', 'delivered')
        ");

        $this->db->query("
            ALTER TABLE cart MODIFY COLUMN status
            ENUM('active', 'checked_out', 'cancelled')
            NOT NULL DEFAULT 'active'
        ");
    }
}
