<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Throwaway diagnostic — verifies seed users and password hashes.
 * Usage: php spark check:login
 */
class CheckLogin extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'check:login';
    protected $description = 'Diagnose login problems by inspecting seeded users.';

    public function run(array $params)
    {
        $db     = \Config\Database::connect();
        $emails = ['admin@nexgear.my.id', 'user@nexgear.my.id'];

        foreach ($emails as $email) {
            $row = $db->table('users')->where('email', $email)->get()->getRowArray();
            CLI::write(str_repeat('=', 60));
            CLI::write("Email: $email", 'yellow');
            if (! $row) {
                CLI::write('  -> NOT FOUND in users table', 'red');
                continue;
            }
            CLI::write("  id:    {$row['id']}");
            CLI::write("  name:  {$row['name']}");
            CLI::write("  role:  {$row['role']}");
            CLI::write("  hash:  {$row['password']}");
            CLI::write('  hash length: ' . strlen($row['password']));
            $verify = password_verify('password', (string) $row['password']);
            CLI::write(
                "  password_verify('password', hash): " . ($verify ? 'TRUE' : 'FALSE'),
                $verify ? 'green' : 'red'
            );
        }

        CLI::write(str_repeat('=', 60));
        $total = $db->table('users')->countAll();
        CLI::write("Total users in DB: {$total}", 'cyan');
    }
}
