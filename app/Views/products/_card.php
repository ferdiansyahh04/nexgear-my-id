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

<style>
    .product-media {
        position: relative;
        overflow: hidden;
    }
    .img-primary, .img-secondary {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 3rem;
        transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        backface-visibility: hidden;
    }
    .img-secondary {
        opacity: 0;
        z-index: 1;
        transform: scale(1.02); /* Start slightly larger for a "settling" effect */
    }
    .img-primary {
        opacity: 1;
        z-index: 2;
        transform: scale(1);
    }
    
    .product-card:hover .img-primary {
        opacity: 0;
        transform: scale(1.08); /* Subtle zoom out for the disappearing image */
    }
    .product-card:hover .img-secondary {
        opacity: 1;
        transform: scale(1.05); /* Gentle zoom in for the appearing image */
    }
    
    .product-card-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 2rem;
        opacity: 0;
        transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        z-index: 3;
    }
    
    .product-card-overlay span {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.25em;
        color: #000;
        text-transform: uppercase;
        transform: translateY(10px);
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        background: rgba(255,255,255,0.8);
        padding: 0.5rem 1rem;
        backdrop-filter: blur(4px);
    }
    
    .product-card:hover .product-card-overlay {
        opacity: 1;
    }
    
    .product-card:hover .product-card-overlay span {
        transform: translateY(0);
    }
    
    .or-cart-btn-minimal {
        transition: all 0.3s ease;
        font-family: 'Space Grotesk', sans-serif;
    }
    .or-cart-btn-minimal:hover:not(:disabled) {
        background-color: #000 !important;
        color: #fff !important;
    }
</style>
