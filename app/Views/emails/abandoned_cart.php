<?php
/**
 * Variables:
 *   $items (array) — list of {id, name, qty, price}
 *   $total (float)
 *   $userName (string)
 */
$rp = static fn (float $n) => 'Rp ' . number_format($n, 0, ',', '.');

$rows = '<table style="width:100%;border-collapse:collapse;margin-top:12px;">';
foreach ($items as $item) {
    $sub = (float) $item['price'] * (int) $item['qty'];
    $rows .= '<tr>'
          .  '<td style="padding:10px 6px;border-bottom:0.5px solid #ddd;">'
          .  esc($item['name']) . ' <span style="color:#888;">×' . (int) $item['qty'] . '</span>'
          .  '</td>'
          .  '<td style="padding:10px 6px;border-bottom:0.5px solid #ddd;" align="right">' . esc($rp($sub)) . '</td>'
          .  '</tr>';
}
$rows .= '<tr><td align="right" style="padding:12px 6px;border-top:1.5px solid #000;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;">Subtotal</td>'
      .  '<td align="right" style="padding:12px 6px;border-top:1.5px solid #000;font-weight:700;font-size:14pt;">' . esc($rp((float) $total)) . '</td></tr>';
$rows .= '</table>';

$body = '<p>Hi ' . esc($userName ?? 'there') . ', here is what you left behind:</p>' . $rows;

echo view('emails/_layout', [
    'title'    => 'You left items in your bag',
    'heading'  => 'Still Thinking?',
    'intro'    => 'Your selection is ready when you are. We held it for you.',
    'body'     => $body,
    'cta'      => ['label' => 'Resume Checkout', 'url' => base_url('/cart')],
    'footnote' => 'Inventory is moving — once it is gone, it is gone.',
]);
