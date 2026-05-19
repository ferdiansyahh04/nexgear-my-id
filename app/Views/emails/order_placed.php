<?php
/**
 * Variables:
 *   $order (array) — cart row
 *   $items (array) — cart_items rows with hydrated `product`
 *   $statusLabel (string)
 */
$rp = static fn (float $n) => 'Rp ' . number_format($n, 0, ',', '.');

$rows = '<table style="width:100%;border-collapse:collapse;margin-top:12px;">'
      . '<thead><tr>'
      . '<th align="left" style="padding:8px 6px;border-bottom:1px solid #000;font-size:9pt;text-transform:uppercase;letter-spacing:0.1em;color:#666;">Item</th>'
      . '<th align="right" style="padding:8px 6px;border-bottom:1px solid #000;font-size:9pt;text-transform:uppercase;letter-spacing:0.1em;color:#666;">Qty</th>'
      . '<th align="right" style="padding:8px 6px;border-bottom:1px solid #000;font-size:9pt;text-transform:uppercase;letter-spacing:0.1em;color:#666;">Total</th>'
      . '</tr></thead><tbody>';

foreach ($items as $item) {
    $name = esc($item['product']['name'] ?? 'Product');
    $sub  = (float) $item['price'] * (int) $item['quantity'];
    $rows .= '<tr>'
          .  '<td style="padding:10px 6px;border-bottom:0.5px solid #ddd;">' . $name . '</td>'
          .  '<td style="padding:10px 6px;border-bottom:0.5px solid #ddd;" align="right">' . (int) $item['quantity'] . '</td>'
          .  '<td style="padding:10px 6px;border-bottom:0.5px solid #ddd;" align="right">' . esc($rp($sub)) . '</td>'
          .  '</tr>';
}

$discount = (float) ($order['discount'] ?? 0);
if ($discount > 0) {
    $rows .= '<tr><td colspan="2" align="right" style="padding:8px 6px;color:#059669;">'
          .  'Discount' . (! empty($order['coupon_code']) ? ' (' . esc($order['coupon_code']) . ')' : '')
          .  '</td><td align="right" style="padding:8px 6px;color:#059669;">− ' . esc($rp($discount)) . '</td></tr>';
}

$rows .= '<tr><td colspan="2" align="right" style="padding:12px 6px;border-top:1.5px solid #000;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;">Grand Total</td>'
      .  '<td align="right" style="padding:12px 6px;border-top:1.5px solid #000;font-weight:700;font-size:14pt;">' . esc($rp((float) $order['total'])) . '</td></tr>';
$rows .= '</tbody></table>';

$intro = 'Thanks for your order. We have received it and will send another note when it ships.';
$body  = '<p><strong>Order #' . (int) $order['id'] . '</strong> &middot; ' . esc($statusLabel ?? 'Placed') . '</p>'
       . '<p>Shipping to: <strong>' . esc($order['shipping_name']) . '</strong>'
       . ', ' . esc($order['shipping_city']) . '</p>'
       . $rows;

echo view('emails/_layout', [
    'title'    => 'Order #' . (int) $order['id'] . ' confirmed',
    'heading'  => 'Order Confirmed',
    'intro'    => $intro,
    'body'     => $body,
    'cta'      => ['label' => 'View Order', 'url' => base_url('/account/orders/' . (int) $order['id'])],
    'footnote' => 'Need help? Reply to this email and we will get back to you.',
]);
