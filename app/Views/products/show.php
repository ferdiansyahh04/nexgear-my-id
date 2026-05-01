<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
/**
 * Helper logic for high-quality fallback images
 */
$productImage = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));

if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
    $name = strtolower($product['name']);
    if (strpos($name, 'keyboard') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'mouse') !== false && strpos($name, 'pad') === false) {
        $productImage = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'headset') !== false || strpos($name, 'audio') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'pad') !== false || strpos($name, 'mat') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1614149162883-504ce4d13909?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'mic') !== false || strpos($name, 'stream') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=1200&auto=format&fit=crop';
    } elseif (strpos($name, 'dock') !== false || strpos($name, 'charger') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=1200&auto=format&fit=crop';
    } else {
        $productImage = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=1200&auto=format&fit=crop';
    }
}
?>

<section class="container py-5" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="row g-5 align-items-center">
        <div class="col-lg-6">
            <div class="product-detail-media" data-aos="zoom-in">
                <img src="<?= $productImage ?>" alt="<?= esc($product['name']) ?>">
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="product-detail-info">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-secondary text-decoration-none small">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('/collection') ?>" class="text-secondary text-decoration-none small">Collection</a></li>
                        <li class="breadcrumb-item active text-white small" aria-current="page"><?= esc($product['name']) ?></li>
                    </ol>
                </nav>
                
                <div class="mb-2">
                    <span class="eyebrow">Hypernex Elite Series</span>
                </div>
                <h1 class="detail-title"><?= esc($product['name']) ?></h1>
                
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="detail-price mb-0">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></div>
                    <div class="stock-chip mb-0">
                        <i class="bi bi-box-seam"></i>
                        <?= esc($product['stock']) ?> units in stock
                    </div>
                </div>
                
                <div class="mb-5">
                    <h2 class="h6 text-white text-uppercase tracking-wider mb-3">Description</h2>
                    <p class="text-secondary lh-lg" style="font-size: 1.1rem;">
                        <?= esc($product['description']) ?>
                    </p>
                </div>
                
                <form action="<?= base_url('/cart/add/' . $product['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="d-grid d-md-flex gap-3">
                        <button class="btn btn-primary-glow px-5 py-3" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus fs-5 me-2"></i>
                            Add to Elite Cart
                        </button>
                        <a href="<?= base_url('/collection') ?>" class="btn btn-soft px-5 py-3">
                            Back to Store
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
    .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255,255,255,0.2);
    }
</style>
<?= $this->endSection() ?>
