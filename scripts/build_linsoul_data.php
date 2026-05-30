<?php
/**
 * Dev-only: regenerate app/Database/Seeds/data/linsoul_iems.json from a fresh
 * snapshot of https://www.linsoul.com/products.json.
 *
 * Linsoul is a Shopify storefront (same feed shape as noirgear.com). We curate
 * a "best of" set of in-ear monitors across price tiers, strip the HTML bodies,
 * convert the USD list price to IDR, and emit the clean JSON the Spark importer
 * (etalase:import-linsoul) consumes.
 *
 * Usage:
 *   curl.exe -s "https://www.linsoul.com/products.json?limit=250" -o linsoul_all.json
 *   php scripts/build_linsoul_data.php
 */

const USD_TO_IDR = 16000;          // retail conversion rate
const SRC        = __DIR__ . '/../linsoul_all.json';
const DEST       = __DIR__ . '/../app/Database/Seeds/data/linsoul_iems.json';

// Curated handles -> store category slug. All IEMs map to the existing
// "headsets" category (the storefront has no dedicated IEM category).
$curated = [
    'tangzu-wan-er-s-g-ii-bass-version-lion-edition' => 'headsets',
    'kiwi-ears-cadenza-ii'                           => 'headsets',
    'dunu-titan-s'                                   => 'headsets',
    'hidizs-mp143-salt'                              => 'headsets',
    'hidizs-mp145'                                   => 'headsets',
    'kiwi-ears-quintet'                              => 'headsets',
    'simgot-supermix-5'                              => 'headsets',
    'kiwi-ears-orchestra-ii'                         => 'headsets',
    'thieaudio-hype-4-mkii'                          => 'headsets',
    'softears-rsv-mkii'                              => 'headsets',
    'thieaudio-monarch-mkiv'                         => 'headsets',
    'tangzu-the-king-wukong'                         => 'headsets',
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
    // Normalise smart punctuation to ASCII, drop emoji/non-BMP.
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

$raw = file_get_contents(SRC);
if ($raw === false) {
    fwrite(STDERR, "Cannot read " . SRC . " — fetch the feed first.\n");
    exit(1);
}
$feed = json_decode($raw, true);
$products = $feed['products'] ?? [];

$byHandle = [];
foreach ($products as $p) {
    $byHandle[$p['handle']] = $p;
}

$out = [];
foreach ($curated as $handle => $category) {
    if (! isset($byHandle[$handle])) {
        fwrite(STDERR, "  ! missing handle in feed: {$handle}\n");
        continue;
    }
    $p = $byHandle[$handle];

    $title = clean_text($p['title']);
    $desc  = clean_text(strip_html($p['body_html'] ?? ''));
    if (mb_strlen($desc) > 600) {
        $desc = mb_substr($desc, 0, 600) . '...';
    }
    if ($desc === '') {
        $desc = "{$title} - high-fidelity in-ear monitor curated from Linsoul.";
    }

    $usd = (float) ($p['variants'][0]['price'] ?? 0);
    $idr = (int) (round($usd * USD_TO_IDR / 1000) * 1000);

    $images = $p['images'] ?? [];
    $img1 = $images[0]['src'] ?? null;
    $img2 = $images[1]['src'] ?? null;

    $out[] = [
        'handle'      => $handle,
        'title'       => $title,
        'category'    => $category,
        'price'       => $idr,
        'description' => $desc,
        'image1_url'  => $img1,
        'image2_url'  => $img2,
    ];
}

if (! is_dir(dirname(DEST))) {
    mkdir(dirname(DEST), 0775, true);
}
file_put_contents(DEST, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo 'Wrote ' . count($out) . ' IEMs to ' . realpath(DEST) . PHP_EOL;
