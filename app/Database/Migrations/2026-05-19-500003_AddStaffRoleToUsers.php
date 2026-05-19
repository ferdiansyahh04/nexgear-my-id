<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Extend the role enum to support a `staff` tier in addition to `admin` and
 * `user`. Existing rows keep their current value.
 */
class AddStaffRoleToUsers extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE users MODIFY COLUMN role
            ENUM('admin','staff','user') NOT NULL DEFAULT 'user'
        ");
    }

    public function down()
    {
        // Promote any 'staff' rows to 'admin' before narrowing the enum.
        $this->db->query("UPDATE users SET role = 'admin' WHERE role = 'staff'");
        $this->db->query("
            ALTER TABLE users MODIFY COLUMN role
            ENUM('admin','user') NOT NULL DEFAULT 'user'
        ");
    }
}
