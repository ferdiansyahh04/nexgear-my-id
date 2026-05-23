<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Repair seed accounts whose passwords were stored as plaintext.
 *
 * Usage:
 *   php spark fix:seed-users
 */
class FixSeedUsers extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'fix:seed-users';
    protected $description = 'Re-hash plaintext passwords and ensure both seed accounts exist.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // Accounts to ensure: email => [password, role, name]
        $accounts = [
            'admin@nexgear.my.id' => ['admin123', 'admin', 'Admin NexGear'],
            'user@nexgear.my.id'  => ['password', 'user',  'Demo User'],
        ];

        foreach ($accounts as $email => [$plain, $role, $name]) {
            $row = $db->table('users')->where('email', $email)->get()->getRowArray();
            $hash = password_hash($plain, PASSWORD_DEFAULT);

            if (! $row) {
                $db->table('users')->insert([
                    'name'       => $name,
                    'email'      => $email,
                    'password'   => $hash,
                    'role'       => $role,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                CLI::write("[CREATED] $email / $plain (role: $role)", 'green');
                continue;
            }

            // If the stored value is already a valid bcrypt hash and verifies, skip.
            $isBcrypt = is_string($row['password'])
                && strlen($row['password']) >= 60
                && (str_starts_with($row['password'], '$2y$') || str_starts_with($row['password'], '$2a$'));

            if ($isBcrypt && password_verify($plain, $row['password'])) {
                CLI::write("[OK] $email already verifies against \"$plain\"", 'cyan');
                continue;
            }

            $db->table('users')
                ->where('id', $row['id'])
                ->update([
                    'password'   => $hash,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            CLI::write("[REHASHED] $email -> use password \"$plain\"", 'yellow');
        }

        CLI::newLine();
        CLI::write('Login credentials are now:', 'green');
        CLI::write('  admin@nexgear.my.id / admin123');
        CLI::write('  user@nexgear.my.id  / password');
    }
}
