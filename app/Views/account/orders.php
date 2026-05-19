<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">OH /</span> Order History
        </h1>
        <a href="<?= base_url('/account/wishlist') ?>" class="account-nav-link">
            Wishlist <span>→</span>
        </a>
    </div>

    <?php if ($orders === []): ?>
        <div class="px-4 px-lg-5 py-5 text-center">
            <p class="font-serif italic text-muted mb-4">No orders yet.</p>
            <a href="<?= base_url('/collection') ?>" class="btn btn-dark px-4 py-3 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.8rem;">
                Start Shopping →
            </a>
        </div>
    <?php else: ?>
        <div class="account-orders-list">
            <?php foreach ($orders as $order):
                $status = $statusMap[$order['status']] ?? ['label' => ucfirst($order['status']), 'tone' => 'muted'];
                $thumb = $order['preview_image']
                    ? base_url('uploads/products/' . esc($order['preview_image']))
                    : 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=200&auto=format&fit=crop';
            ?>
                <a href="<?= base_url('/account/orders/' . (int) $order['id']) ?>" class="account-order-card">
                    <div class="account-order-thumb">
                        <img src="<?= $thumb ?>" alt="" loading="lazy">
                    </div>
                    <div class="account-order-meta">
                        <div class="account-order-id">Order #<?= (int) $order['id'] ?></div>
                        <div class="account-order-date">
                            <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                        </div>
                        <div class="account-order-summary">
                            <?= (int) ($order['unique_count'] ?? 0) ?> item<?= ($order['unique_count'] ?? 0) === 1 ? '' : 's' ?>
                            · <?= (int) ($order['item_count'] ?? 0) ?> unit<?= ($order['item_count'] ?? 0) === 1 ? '' : 's' ?>
                        </div>
                    </div>
                    <div class="account-order-total font-serif italic">
                        Rp <?= number_format((float) $order['total'], 0, ',', '.') ?>
                    </div>
                    <span class="status-pill status-tone-<?= esc($status['tone']) ?>">
                        <?= esc($status['label']) ?>
                    </span>
                    <span class="account-order-arrow">→</span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($pager && $pager->getPageCount() > 1): ?>
            <div class="container-fluid px-4 px-lg-5 py-4 d-flex justify-content-center">
                <?= $pager->links('default', 'default_full') ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
