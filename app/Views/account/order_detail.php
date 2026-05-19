<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$status = $statusMap[$order['status']] ?? ['label' => ucfirst($order['status']), 'tone' => 'muted', 'description' => ''];
?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="px-4 px-lg-5 py-4 border-bottom border-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="text-uppercase text-muted fw-bold mb-1" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                Order
            </div>
            <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.5rem; letter-spacing: -0.02em; font-weight: 700;">
                #<?= (int) $order['id'] ?>
            </h1>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="status-pill status-tone-<?= esc($status['tone']) ?>"><?= esc($status['label']) ?></span>
            <a href="<?= base_url('/account/orders') ?>" class="account-nav-link">← Back to Orders</a>
        </div>
    </div>

    <!-- Timeline / progress stepper -->
    <div class="px-4 px-lg-5 py-4 border-bottom border-dark">
        <div class="order-timeline">
            <?php foreach ($timeline as $i => $stage): ?>
                <div class="order-timeline-step is-<?= esc($stage['state']) ?>">
                    <div class="order-timeline-dot"><?= $i + 1 ?></div>
                    <div class="order-timeline-label"><?= esc($stage['label']) ?></div>
                </div>
                <?php if ($i < count($timeline) - 1): ?>
                    <div class="order-timeline-line is-<?= esc($stage['state']) ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <p class="text-muted font-serif italic mt-3 mb-0" style="font-size: 0.9rem;">
            <?= esc($status['description']) ?>
        </p>
    </div>

    <div class="row g-0">
        <!-- Items -->
        <div class="col-lg-8 border-end-lg border-dark">
            <div class="px-4 px-lg-5 py-4">
                <h2 class="text-uppercase fw-bold mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                    Items
                </h2>
                <?php foreach ($items as $item):
                    $img = $item['product']
                        ? base_url('uploads/products/' . esc($item['product']['image'] ?: 'default-product.svg'))
                        : 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=120&auto=format&fit=crop';
                ?>
                    <div class="order-item-row">
                        <div class="order-item-thumb">
                            <img src="<?= $img ?>" alt="">
                        </div>
                        <div class="order-item-info">
                            <div class="order-item-name">
                                <?php if ($item['product']): ?>
                                    <a href="<?= base_url('/products/' . (int) $item['product']['id']) ?>">
                                        <?= esc($item['product']['name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Product no longer available</span>
                                <?php endif; ?>
                            </div>
                            <div class="order-item-meta">
                                Qty: <?= (int) $item['quantity'] ?>
                                · Unit: Rp <?= number_format((float) $item['price'], 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="order-item-subtotal font-serif italic">
                            Rp <?= number_format((float) $item['price'] * (int) $item['quantity'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 border-start-lg border-dark">
            <div class="p-4 p-lg-5">
                <h2 class="text-uppercase fw-bold mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                    Shipping
                </h2>
                <div class="mb-4">
                    <div class="text-muted small text-uppercase fw-bold mb-1" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Recipient</div>
                    <div><?= esc($order['shipping_name']) ?></div>
                    <div class="text-muted small"><?= esc($order['shipping_phone']) ?></div>
                </div>
                <div class="mb-4">
                    <div class="text-muted small text-uppercase fw-bold mb-1" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Address</div>
                    <div style="line-height: 1.6;">
                        <?= nl2br(esc($order['shipping_address'])) ?><br>
                        <?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_postal_code']) ?>
                    </div>
                </div>

                <hr class="border-dark border-opacity-25 my-4">

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Paid</span>
                    <span class="font-serif italic h4 mb-0">
                        Rp <?= number_format((float) $order['total'], 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
