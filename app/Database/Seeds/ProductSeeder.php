<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Fixed demo products (guaranteed baseline)
        $fixed = [
            ['name' => 'Nebula K87 Mechanical Keyboard',  'description' => 'Compact hot-swappable keyboard with RGB lighting and tactile switches.',        'price' => 899000, 'stock' => 14],
            ['name' => 'Pulsefire Ultra Mouse',            'description' => 'Lightweight wireless mouse with low-latency sensor and textured side grip.',    'price' => 549000, 'stock' => 22],
            ['name' => 'EchoStrike 7.1 Headset',           'description' => 'Closed-back headset with virtual surround, soft pads, and detachable mic.',    'price' => 729000, 'stock' => 18],
            ['name' => 'Orbit RGB Mousepad XL',            'description' => 'Wide desk mat with stitched edges, soft glide surface, and RGB edge lighting.', 'price' => 319000, 'stock' => 30],
            ['name' => 'Aegis Controller Dock',            'description' => 'Dual charging dock for wireless controllers with LED battery indicators.',      'price' => 279000, 'stock' => 16],
            ['name' => 'Vector Stream Mic',                'description' => 'USB condenser microphone with cardioid pickup and tap-to-mute control.',        'price' => 639000, 'stock' => 12],
        ];

        $rows = [];
        foreach ($fixed as $item) {
            $rows[] = array_merge($item, [
                'image'      => 'default-product.svg',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Generate extra random products with Faker (if available)
        if (class_exists(Factory::class)) {
            $faker = Factory::create('id_ID');
            $categories = ['Keyboard', 'Mouse', 'Headset', 'Controller', 'Monitor', 'Webcam', 'Mic', 'Mousepad'];

            for ($i = 0; $i < 12; $i++) {
                $cat = $faker->randomElement($categories);
                $rows[] = [
                    'name'        => $faker->company() . ' ' . $cat . ' ' . strtoupper($faker->bothify('??##')),
                    'description' => $faker->sentence(12),
                    'price'       => $faker->numberBetween(15, 200) * 10000,
                    'stock'       => $faker->numberBetween(0, 50),
                    'image'       => 'default-product.svg',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->table('products')->insertBatch($rows);
        echo "  ✓ Seeded " . count($rows) . " products\n";
    }
}
