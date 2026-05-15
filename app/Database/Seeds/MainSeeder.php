<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

/**
 * Main Seeder — Seeds all tables with demo data.
 *
 * Usage: php spark db:seed MainSeeder
 */
class MainSeeder extends Seeder
{
    public function run()
    {
        $this->call('UserSeeder');
        $this->call('ProductSeeder');
    }
}
