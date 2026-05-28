<?php

namespace App\Commands;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Import keyboards and mice scraped from noirgear.com into the etalase.
 *
 * Reads `app/Database/Seeds/data/noirgear_kb_mouse.json`, downloads each
 * product image into `public/uploads/products/`, and inserts (or updates)
 * a row in `products` so the storefront has live merch ready to ship.
 *
 * Usage:
 *   php spark etalase:import-noirgear
 *
 * Options:
 *   --refresh   Wipe imported rows first (matches by `name` prefix "Noir " and "Neo ").
 *   --no-images Skip image downloads (use existing files / placeholder).
 */
class ImportNoirgear extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'etalase:import-noirgear';
    protected $description = 'Import keyboards & mice from noirgear.com into the products table.';
    protected $usage       = 'etalase:import-noirgear [--refresh] [--no-images]';
    protected $options     = [
        '--refresh'   => 'Delete previously-imported Noir/Neo rows before re-importing.',
        '--no-images' => 'Skip downloading product images.',
    ];

    public function run(array $params)
    {
        $jsonFile = APPPATH . 'Database/Seeds/data/noirgear_kb_mouse.json';
        if (! is_file($jsonFile)) {
            CLI::error("Seed data not found at {$jsonFile}");
            CLI::write('Run scripts/build_seeder_data.ps1 first to generate it.', 'yellow');
            return EXIT_ERROR;
        }

        $items = json_decode((string) file_get_contents($jsonFile), true);
        if (! is_array($items) || $items === []) {
            CLI::error('JSON did not parse to a list of products.');
            return EXIT_ERROR;
        }

        $refresh   = CLI::getOption('refresh') !== null;
        $noImages  = CLI::getOption('no-images') !== null;

        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();

        // Resolve category ids
        $cats = [];
        foreach ($categoryModel->findAll() as $row) {
            $cats[$row['slug']] = (int) $row['id'];
        }
        if (! isset($cats['keyboards'], $cats['mice'])) {
            CLI::error('Required categories (keyboards, mice) are missing — run migrations first.');
            return EXIT_ERROR;
        }

        // Optional cleanup
        if ($refresh) {
            $deleted = $productModel
                ->groupStart()
                    ->like('name', 'Noir ', 'after')
                    ->orLike('name', 'Neo ', 'after')
                ->groupEnd()
                ->delete();
            CLI::write("[refresh] Removed previously imported rows.", 'yellow');
        }

        $uploads = FCPATH . 'uploads/products/';
        if (! is_dir($uploads)) {
            mkdir($uploads, 0775, true);
        }

        $imported = 0;
        $updated  = 0;
        foreach ($items as $item) {
            $name = trim((string) ($item['title']  ?? ''));
            $cat  = (string) ($item['category'] ?? '');
            if ($name === '' || ! isset($cats[$cat])) {
                CLI::write("Skipping malformed entry: {$name}", 'yellow');
                continue;
            }

            $primaryFile   = $this->materializeImage($item['image1_url'] ?? null, (string) ($item['handle'] ?? ''), 'a', $uploads, $noImages);
            $secondaryFile = $this->materializeImage($item['image2_url'] ?? null, (string) ($item['handle'] ?? ''), 'b', $uploads, $noImages);

            $payload = [
                'name'            => $name,
                'description'     => (string) ($item['description'] ?? ''),
                'category_id'     => $cats[$cat],
                'price'           => (int) round((float) ($item['price'] ?? 0)),
                'stock'           => random_int(8, 35),
                'image'           => $primaryFile,
                'image_secondary' => $secondaryFile,
            ];

            $existing = $productModel->where('name', $name)->first();
            if ($existing) {
                $productModel->update((int) $existing['id'], $payload);
                CLI::write("  ↻ Updated  {$name}", 'cyan');
                $updated++;
            } else {
                $productModel->insert($payload);
                CLI::write("  + Imported {$name}", 'green');
                $imported++;
            }
        }

        CLI::newLine();
        CLI::write("Done — imported {$imported}, updated {$updated}, total = " . ($imported + $updated), 'green');
        return EXIT_SUCCESS;
    }

    /**
     * Download $url into public/uploads/products/ if it isn't already there,
     * returning the relative filename to store on the product.
     *
     * Falls back gracefully when downloads are disabled or the URL is empty.
     */
    private function materializeImage(?string $url, string $slug, string $suffix, string $uploads, bool $noImages): string
    {
        $url = is_string($url) ? trim($url) : '';
        if ($url === '') {
            return 'default-product.svg';
        }

        // Build a deterministic local filename from the handle + suffix
        $ext = 'jpg';
        if (preg_match('/\.([a-z0-9]{2,4})(?:\?|$)/i', $url, $m)) {
            $candidate = strtolower($m[1]);
            if (in_array($candidate, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $ext = $candidate === 'jpeg' ? 'jpg' : $candidate;
            }
        }
        $base = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($slug));
        $base = trim((string) $base, '-');
        if ($base === '') {
            $base = 'noirgear-' . substr(md5($url), 0, 8);
        }
        $filename = "{$base}-{$suffix}.{$ext}";
        $target   = $uploads . $filename;

        if (is_file($target) && filesize($target) > 1024) {
            return $filename;
        }
        if ($noImages) {
            return is_file($target) ? $filename : 'default-product.svg';
        }

        // cURL download with a short timeout. Skip if it fails.
        // SSL verification is loosened only in development because Windows PHP
        // installs frequently ship without a CA bundle. In production we keep
        // the verification on — Linux distros provide a CA bundle out of the box.
        $verifyPeer = ENVIRONMENT !== 'development';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'NexGearImporter/1.0',
            CURLOPT_SSL_VERIFYPEER => $verifyPeer,
            CURLOPT_SSL_VERIFYHOST => $verifyPeer ? 2 : 0,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $code >= 400 || strlen($body) < 1024) {
            CLI::write("    ! image download failed for {$url} (HTTP {$code} {$err})", 'red');
            return 'default-product.svg';
        }

        file_put_contents($target, $body);
        return $filename;
    }
}
