<?php
/**
 * Quick View Partial — rendered into a Bootstrap modal via AJAX.
 *
 * Variables:
 *   $product (array) — product row
 */
$primary = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));

if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
    $name = strtolower($product['name']);
    if (strpos($name, 'keyboard') !== false) {
        $primary = 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'mouse') !== false) {
        $primary = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'headset') !== false || strpos($name, 'audio') !== false) {
        $primary = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'mic') !== false || strpos($name, 'stream') !== false) {
        $primary = 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=1200&auto=format&fit=crop';
    } else {
        $primary = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=1200&auto=format&fit=crop';
    }
}

$inStock = (int) $product['stock'] >= 1;
?>
<div class="row g-0 quick-view-grid">
    <div class="col-md-6 quick-view-media">
        <img src="<?= $primary ?>" alt="<?= esc($product['name']) ?>" loading="lazy">
    </div>
    <div class="col-md-6 quick-view-body">
        <div class="quick-view-eyebrow">
            <span class="font-serif me-2">QV /</span> Quick Look
        </div>
        <h2 class="quick-view-title"><?= esc($product['name']) ?></h2>
        <div class="quick-view-price font-serif">
            Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
        </div>

        <p class="quick-view-text">
            <?= esc(mb_strimwidth((string) $product['description'], 0, 220, '…')) ?>
        </p>

        <div class="quick-view-meta">
            <span><?= esc($product['stock']) ?> units in vault</span>
            <span class="font-serif italic">UID #<?= (int) $product['id'] ?></span>
        </div>

        <?php if ($inStock): ?>
            <form action="<?= base_url('/cart/add/' . (int) $product['id']) ?>" method="post"
                  class="ajax-add-to-cart quick-view-form" data-source="quick-view">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-4"
                        style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">
                    <span class="btn-text">Add to Bag</span>
                    <span class="btn-icon">→</span>
                </button>
            </form>
        <?php else: ?>
            <div class="bg-dark text-white text-center py-3 text-uppercase fw-bold"
                 style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">
                Vault Empty / Sold Out
            </div>
        <?php endif; ?>

        <a href="<?= base_url('/products/' . (int) $product['id']) ?>" class="quick-view-link">
            View Full Specifications <span>→</span>
        </a>
    </div>
</div>
