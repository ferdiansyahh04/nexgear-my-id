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
    <a href="<?= base_url('/products/' . $product['id']) ?>" class="d-block product-media-container position-relative overflow-hidden" style="aspect-ratio: 1/1; background: #fff;">
        <div class="h-100 w-100 p-5 d-flex align-items-center justify-content-center product-media">
            <!-- Primary Image -->
            <img src="<?= $primaryImage ?>" alt="<?= esc($product['name']) ?>" class="img-primary">
            
            <!-- Secondary Image (Hover) -->
            <?php if ($secondaryImage): ?>
                <img src="<?= $secondaryImage ?>" alt="<?= esc($product['name']) ?> Hover" class="img-secondary">
            <?php endif; ?>
        </div>
        
        <!-- Quick View Overlay -->
        <div class="product-card-overlay">
            <span class="text-uppercase fw-bold small" style="letter-spacing: 0.15em;">View Products</span>
        </div>
    </a>
    
    <!-- Info + Price Row -->
    <div class="p-3 px-4 border-top border-dark d-flex justify-content-between align-items-center bg-white">
        <h3 class="mb-0 text-uppercase fw-bold" style="font-size: 0.75rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">
            <a href="<?= base_url('/products/' . $product['id']) ?>" class="text-dark text-decoration-none"><?= esc($product['name']) ?></a>
        </h3>
        <span class="text-dark font-serif italic" style="font-size: 0.9rem;">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></span>
    </div>
    
    <!-- Add to Bag (Clean Minimalist Button) -->
    <div class="border-top border-dark mt-auto bg-white">
        <form action="<?= base_url('/cart/add/' . $product['id']) ?>" method="post" class="m-0 ajax-add-to-cart">
            <?= csrf_field() ?>
            <button class="w-100 bg-transparent border-0 py-3 px-4 text-center text-uppercase text-dark or-cart-btn-minimal" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                <span class="btn-text small fw-bold" style="letter-spacing: 0.2em; font-size: 0.65rem;">
                    <?= (int) $product['stock'] < 1 ? 'SOLD OUT' : 'ADD TO BAG' ?>
                </span>
            </button>
        </form>
    </div>
</article>


