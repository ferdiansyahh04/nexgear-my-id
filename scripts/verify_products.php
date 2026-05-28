<?php
$mysqli = new mysqli('localhost', 'root', '', 'nexgear_store');
if ($mysqli->connect_error) {
    fwrite(STDERR, $mysqli->connect_error);
    exit(1);
}

$res = $mysqli->query("
    SELECT p.id, p.name, c.slug AS category, p.price, p.stock, p.image, p.image_secondary
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE c.slug IN ('keyboards', 'mice')
    ORDER BY c.slug, p.id
");

$grouped = ['keyboards' => [], 'mice' => []];
while ($r = $res->fetch_assoc()) {
    $grouped[$r['category']][] = $r;
}

foreach ($grouped as $cat => $rows) {
    echo PHP_EOL . strtoupper($cat) . ' (' . count($rows) . ')' . PHP_EOL;
    foreach ($rows as $r) {
        $primaryOk   = is_file(__DIR__ . '/../public/uploads/products/' . $r['image']) ? 'OK' : 'MISS';
        $secondaryOk = $r['image_secondary'] ? (is_file(__DIR__ . '/../public/uploads/products/' . $r['image_secondary']) ? 'OK' : 'MISS') : 'none';
        printf("  #%d  %-90s  Rp %10s  stock=%2d  img1=%s  img2=%s\n",
            $r['id'], substr($r['name'], 0, 88), number_format((float) $r['price'], 0, ',', '.'),
            $r['stock'], $primaryOk, $secondaryOk);
    }
}

echo PHP_EOL . 'Total products: ';
$res = $mysqli->query("SELECT COUNT(*) c FROM products");
echo $res->fetch_assoc()['c'] . PHP_EOL;
