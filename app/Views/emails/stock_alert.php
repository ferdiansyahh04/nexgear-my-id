<?php
/**
 * Variables:
 *   $product (array)
 */
$body = '<p><strong>' . esc($product['name']) . '</strong> is back in the vault.</p>'
      . '<p>Stock is limited and tends to move quickly — secure yours now.</p>';

echo view('emails/_layout', [
    'title'    => 'Back in stock: ' . $product['name'],
    'heading'  => 'It Returned',
    'intro'    => 'You asked us to ping you when this came back. Consider yourself pinged.',
    'body'     => $body,
    'cta'      => ['label' => 'View Product', 'url' => base_url('/products/' . (int) $product['id'])],
    'footnote' => 'You will only get this once per restock cycle.',
]);
