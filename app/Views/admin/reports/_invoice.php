<?php
/**
 * Invoice template rendered by Dompdf. Standalone HTML — no layout extends,
 * no external network assets (Dompdf chokes on CDN occasionally; we keep
 * styles inline + PDF-friendly fonts only).
 */
$itemsTotal = 0;
foreach ($items as $it) {
    $itemsTotal += (float) $it['price'] * (int) $it['quantity'];
}
$discount = (float) ($order['discount'] ?? 0);
$total    = (float) $order['total'];
$rp = static fn (float $n) => 'Rp ' . number_format($n, 0, ',', '.');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #<?= (int) $order['id'] ?></title>
    <style>
        @page { margin: 24mm 18mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #000; font-size: 11pt; line-height: 1.5; margin: 0; }
        .row { display: table; width: 100%; }
        .col { display: table-cell; vertical-align: top; }
        .brand { font-size: 28pt; font-weight: 700; letter-spacing: -1pt; text-transform: uppercase; }
        .meta-label { font-size: 8pt; color: #666; text-transform: uppercase; letter-spacing: 1.5pt; margin-bottom: 2pt; }
        .meta-value { font-size: 11pt; }
        .divider { border-top: 1.5pt solid #000; margin: 18pt 0; height: 0; }
        h2.section { font-size: 9pt; text-transform: uppercase; letter-spacing: 1.5pt; color: #666; margin: 0 0 8pt 0; font-weight: 700; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 12pt; }
        table.items thead th { font-size: 8.5pt; text-transform: uppercase; letter-spacing: 1pt; color: #666; padding: 8pt 6pt; border-bottom: 1pt solid #000; text-align: left; }
        table.items tbody td { padding: 10pt 6pt; border-bottom: 0.5pt solid #ddd; vertical-align: top; }
        .text-end { text-align: right; }
        .total-row td { padding-top: 6pt; padding-bottom: 6pt; }
        .grand td { font-size: 14pt; font-weight: 700; border-top: 1.5pt solid #000; padding-top: 10pt; }
        .pill { display: inline-block; padding: 2pt 8pt; border: 1pt solid #000; font-size: 8pt; text-transform: uppercase; letter-spacing: 1pt; }
        .footer { margin-top: 28pt; font-size: 8pt; color: #666; text-align: center; }
    </style>
</head>
<body>

<div class="row">
    <div class="col">
        <div class="brand">NEXGEAR</div>
        <div style="font-size: 8pt; color: #666; letter-spacing: 1.5pt; text-transform: uppercase;">A Modern Expression of Precision</div>
    </div>
    <div class="col text-end">
        <div class="meta-label">Invoice</div>
        <div style="font-size: 18pt; font-weight: 700;">#<?= (int) $order['id'] ?></div>
        <div class="meta-label" style="margin-top: 8pt;">Issued</div>
        <div class="meta-value"><?= esc($generatedAt) ?></div>
    </div>
</div>

<div class="divider"></div>

<div class="row">
    <div class="col" style="width: 50%; padding-right: 12pt;">
        <h2 class="section">Bill To</h2>
        <div style="font-weight: 700;"><?= esc($order['shipping_name']) ?></div>
        <div><?= esc($order['shipping_phone']) ?></div>
        <div style="margin-top: 4pt;"><?= nl2br(esc($order['shipping_address'])) ?></div>
        <div><?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_postal_code']) ?></div>
    </div>
    <div class="col text-end">
        <h2 class="section">Status</h2>
        <span class="pill"><?= esc($statusInfo['label']) ?></span>
        <div style="margin-top: 12pt;">
            <span class="meta-label">Order placed</span><br>
            <?= esc(date('d M Y, H:i', strtotime($order['created_at']))) ?>
        </div>
    </div>
</div>

<h2 class="section" style="margin-top: 20pt;">Items</h2>
<table class="items">
    <thead>
        <tr>
            <th style="width: 50%;">Product</th>
            <th class="text-end" style="width: 15%;">Unit Price</th>
            <th class="text-end" style="width: 10%;">Qty</th>
            <th class="text-end" style="width: 25%;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <div style="font-weight: 700;"><?= esc($item['product_name'] ?? 'Removed product') ?></div>
                </td>
                <td class="text-end"><?= $rp((float) $item['price']) ?></td>
                <td class="text-end"><?= (int) $item['quantity'] ?></td>
                <td class="text-end"><?= $rp((float) $item['price'] * (int) $item['quantity']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($items === []): ?>
            <tr><td colspan="4" style="padding: 20pt; text-align: center; color: #999; font-style: italic;">No items.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<table class="items" style="border-collapse: collapse;">
    <tbody>
        <tr class="total-row">
            <td style="width: 75%;"></td>
            <td style="text-align: right; color: #666; padding-right: 6pt;">Subtotal</td>
            <td class="text-end" style="width: 25%;"><?= $rp($itemsTotal) ?></td>
        </tr>
        <?php if ($discount > 0): ?>
            <tr class="total-row">
                <td></td>
                <td style="text-align: right; color: #059669; padding-right: 6pt;">
                    Discount<?= ! empty($order['coupon_code']) ? ' (' . esc($order['coupon_code']) . ')' : '' ?>
                </td>
                <td class="text-end" style="color: #059669;">− <?= $rp($discount) ?></td>
            </tr>
        <?php endif; ?>
        <tr class="total-row">
            <td></td>
            <td style="text-align: right; color: #666; padding-right: 6pt;">Shipping</td>
            <td class="text-end">FREE</td>
        </tr>
        <tr class="grand">
            <td></td>
            <td style="text-align: right; padding-right: 6pt; text-transform: uppercase; letter-spacing: 1pt;">Grand Total</td>
            <td class="text-end"><?= $rp($total) ?></td>
        </tr>
    </tbody>
</table>

<div class="footer">
    Thank you for shopping with NexGear.
    Questions? Reach us at hello@nexgear.my.id.
    <br>This invoice was generated automatically. No signature required.
</div>

</body>
</html>
