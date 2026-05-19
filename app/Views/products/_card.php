<?php
/**
 * Logic for Primary and Secondary (Hover) images
 */
$primaryImage = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));
$secondaryImage = !empty($product['image_secondary']) 
    ? base_url('uploads/products/' . esc($product['image_secondary'])) 
    : '';

// Refined Fallback Logic: Always provide a secondary image if empty
if (empty($secondaryImage)) {
    $name = strtolower($product['name']);
    
    // Default fallback pairs from Unsplash
    if (strpos($name, 'keyboard') !== false) {
        if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
            $primaryImage = 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=800&auto=format&fit=crop';
        }
        $secondaryImage = 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?q=80&w=800&auto=format&fit=crop';
    } elseif (strpos($name, 'mouse') !== false) {
        if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
            $primaryImage = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=800&auto=format&fit=crop';
        }
        $secondaryImage = 'https://images.unsplash.com/photo-1617325247661-6750456102d9?q=80&w=800&auto=format&fit=crop';
    } elseif (strpos($name, 'headset') !== false || strpos($name, 'audio') !== false) {
        if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
            $primaryImage = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800&auto=format&fit=crop';
        }
        $secondaryImage = 'https://images.unsplash.com/photo-1484704849700-f032a568e944?q=80&w=800&auto=format&fit=crop';
    } else {
        if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
            $primaryImage = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=800&auto=format&fit=crop';
        }
        $secondaryImage = 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=800&auto=format&fit=crop';
    }
}
?>

<article class="product-card or-card-elevated h-100 d-flex flex-column border-0">
    <!-- Image Area -->
    <div class="product-media-shell position-relative">
        <a href="<?= base_url('/products/' . $product['id']) ?>" class="d-block product-media-container position-relative overflow-hidden">
            <div class="h-100 w-100 p-5 d-flex align-items-center justify-content-center product-media">
                <!-- Primary Image -->
                <img src="<?= $primaryImage ?>" alt="<?= esc($product['name']) ?>" class="img-primary">
                
                <!-- Secondary Image (Hover) -->
                <?php if ($secondaryImage): ?>
                    <img src="<?= $secondaryImage ?>" alt="<?= esc($product['name']) ?> Hover" class="img-secondary">
                <?php endif; ?>
            </div>
        </a>

        <!-- Wishlist heart toggle -->
        <?php $wlActive = (new \App\Libraries\WishlistService())->has((int) $product['id']); ?>
        <button type="button"
                class="wishlist-toggle <?= $wlActive ? 'is-active' : '' ?>"
                data-wishlist-toggle="<?= (int) $product['id'] ?>"
                aria-label="<?= $wlActive ? 'Remove from wishlist' : 'Save to wishlist' ?>"
                title="Save to wishlist">
            <i class="bi <?= $wlActive ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
        </button>
        
        <!-- Quick View Overlay (sibling of anchor — valid HTML) -->
        <div class="product-card-overlay">
            <button type="button" class="quick-view-trigger text-uppercase fw-bold small"
                    data-product-id="<?= (int) $product['id'] ?>"
                    style="letter-spacing: 0.15em;">
                Quick View
            </button>
        </div>
    </div>
    
    <!-- Info + Price Row -->
    <div class="product-card-info p-3 px-4 border-top border-dark d-flex justify-content-between align-items-center">
        <h3 class="mb-0 text-uppercase fw-bold" style="font-size: 0.75rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">
            <a href="<?= base_url('/products/' . $product['id']) ?>" class="product-card-name-link text-decoration-none"><?= esc($product['name']) ?></a>
        </h3>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="compare-toggle" data-compare-toggle="<?= (int) $product['id'] ?>"
                    aria-label="Toggle compare for <?= esc($product['name']) ?>" title="Compare">
                <i class="bi bi-bar-chart-steps"></i>
            </button>
            <span class="product-card-price font-serif italic" style="font-size: 0.9rem;">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></span>
        </div>
    </div>
    
    <!-- Add to Bag (Clean Minimalist Button) -->
    <div class="product-card-cta border-top border-dark mt-auto">
        <form action="<?= base_url('/cart/add/' . $product['id']) ?>" method="post" class="m-0 ajax-add-to-cart">
            <?= csrf_field() ?>
            <button class="w-100 bg-transparent border-0 py-3 px-4 text-center text-uppercase or-cart-btn-minimal" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                <span class="btn-text small fw-bold" style="letter-spacing: 0.2em; font-size: 0.65rem;">
                    <?= (int) $product['stock'] < 1 ? 'SOLD OUT' : 'ADD TO BAG' ?>
                </span>
            </button>
        </form>
    </div>
</article>


