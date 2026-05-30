<?php
/**
 * Dev-only: regenerate app/Database/Seeds/data/deskmat_mousepads.json from fresh
 * snapshots of two Shopify storefronts:
 *   - https://pressplayid.com/collections/deskmat/products.json
 *   - https://www.noirgear.com/collections/deskmat-mousepad/products.json
 *
 * Curates a "best of" set of mousepads / deskmats across both stores, strips
 * the HTML bodies, and emits the clean JSON the Spark importer
 * (etalase:import-deskmat) consumes. Both stores price in IDR, so no currency
 * conversion is needed.
 *
 * Usage:
 *   curl.exe -s "https://pressplayid.com/collections/deskmat/products.json?limit=250" -o pressplay_deskmat.json
 *   curl.exe -s "https://www.noirgear.com/collections/deskmat-mousepad/products.json?limit=250" -o noir_deskmat.json
 *   php scripts/build_deskmat_data.php
 */

const DEST = __DIR__ . '/../app/Database/Seeds/data/deskmat_mousepads.json';

// Curated handles per source feed. All map to the "mousepads" category.
$sources = [
    'pressplay_deskmat.json' => [
        'press-play-x-demon-slayer-nichirin-40x90-gaming-deskmat-mousepad',
        'press-play-x-demon-slayer-ensemble-40x90-gaming-deskmat-mousepad',
        'palette-series-gaming-mousepad-deskmat-by-press-play',
        'ventus-gaming-mousepad-35x90cm-5mm-thick',
        'cutting-mat-gaming-mousepad-deskmat',
        'refract-gaming-mousepad-deskmat',
        'spacebit-gaming-mousepad-deskmat-by-press-play',
        'liquid-series-gaming-mousepad-deskmat-by-press-play',
        'kuronami-gaming-mousepad-by-press-play',
    ],
    'noir_deskmat.json' => [
        'noir-one-mouse-pad-gaming-mousepad-high-fast-medium-balanced-slow-control-speed',
        'noir-metro-edge-deskmat',
        'noir-wave-series-deskmat',
        'noir-alpine-bloom-deskmat',
    ],
];

function strip_html(string $html): string
{
    $t = preg_replace('/<[^>]+>/', ' ', $html);
    $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $t = preg_replace('/\s+/u', ' ', $t);
    return trim((string) $t);
}

function clean_text(string $s): string
{
    $map = [
        "\xE2\x80\x94" => '-', "\xE2\x80\x93" => '-',
        "\xE2\x80\x99" => "'", "\xE2\x80\x98" => "'",
        "\xE2\x80\x9C" => '"', "\xE2\x80\x9D" => '"',
        "\xC2\xA0" => ' ',
    ];
    $s = strtr($s, $map);
    $s = preg_replace('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{2190}-\x{21FF}\x{2B00}-\x{2BFF}]/u', '', $s);
    return trim((string) preg_replace('/\s+/u', ' ', $s));
}

$out = [];
foreach ($sources as $file => $handles) {
    $path = __DIR__ . '/../' . $file;
    $raw  = file_get_contents($path);
    if ($raw === false) {
        fwrite(STDERR, "Cannot read {$file} — fetch the feed first.\n");
        continue;
    }
    $feed   = json_decode($raw, true);
    $byHandle = [];
    foreach ($feed['products'] ?? [] as $p) {
        $byHandle[$p['handle']] = $p;
    }

    foreach ($handles as $handle) {
        if (! isset($byHandle[$handle])) {
            fwrite(STDERR, "  ! missing handle in {$file}: {$handle}\n");
            continue;
        }
        $p = $byHandle[$handle];

        $title = clean_text($p['title']);
        $desc  = clean_text(strip_html($p['body_html'] ?? ''));
        if (mb_strlen($desc) > 600) {
            $desc = mb_substr($desc, 0, 600) . '...';
        }
        if ($desc === '') {
            $desc = "{$title} - premium deskmat / mousepad.";
        }

        $price  = (int) round((float) ($p['variants'][0]['price'] ?? 0));
        $images = $p['images'] ?? [];

        $out[] = [
            'handle'      => $handle,
            'title'       => $title,
            'category'    => 'mousepads',
            'price'       => $price,
            'description' => $desc,
            'image1_url'  => $images[0]['src'] ?? null,
            'image2_url'  => $images[1]['src'] ?? null,
        ];
    }
}

if (! is_dir(dirname(DEST))) {
    mkdir(dirname(DEST), 0775, true);
}
file_put_contents(DEST, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo 'Wrote ' . count($out) . ' mousepads to ' . realpath(DEST) . PHP_EOL;
