<?php
/**
 * Helper logic for high-quality fallback images
 */
$productImage = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));

if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
    $name = strtolower($product['name']);
    if (strpos($name, 'keyboard') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=800&auto=format&fit=crop';
    } elseif (strpos($name, 'mouse') !== false && strpos($name, 'pad') === false) {
        $productImage = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=800&auto=format&fit=crop';
    } elseif (strpos($name, 'headset') !== false || strpos($name, 'audio') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800&auto=format&fit=crop';
    } elseif (strpos($name, 'pad') !== false || strpos($name, 'mat') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1614149162883-504ce4d13909?q=80&w=800&auto=format&fit=crop';
    } elseif (strpos($name, 'mic') !== false || strpos($name, 'stream') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=800&auto=format&fit=crop';
    } elseif (strpos($name, 'dock') !== false || strpos($name, 'charger') !== false) {
        $productImage = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=800&auto=format&fit=crop';
    } else {
        $productImage = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=800&auto=format&fit=crop';
    }
}
?>

<div class="col-sm-6 col-lg-4 d-flex">
    <article class="product-card" data-aos="fade-up">
        <div class="product-media-container">
            <a href="<?= base_url('/products/' . $product['id']) ?>" class="product-media d-block">
                <img src="<?= $productImage ?>" alt="<?= esc($product['name']) ?>" loading="lazy">
            </a>
            <div class="product-status-badge">
                <span class="badge-pill">In Stock</span>
            </div>
        </div>
        
        <div class="product-body">
            <div class="product-info mb-4">
                <h3 class="h5 mb-2">
                    <a href="<?= base_url('/products/' . $product['id']) ?>" class="text-white text-decoration-none"><?= esc($product['name']) ?></a>
                </h3>
                <p class="text-muted small mb-0"><?= esc(character_limiter($product['description'] ?? '', 55)) ?></p>
            </div>
            
            <div class="product-footer mt-auto pt-3 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
                <div class="product-price">
                    <span class="text-muted small d-block mb-1">Elite Gear</span>
                    <span class="fw-bold text-white">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></span>
                </div>
                
                <form action="<?= base_url('/cart/add/' . $product['id']) ?>" method="post" class="m-0">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary-glow btn-sm p-2" style="width: 40px; height: 40px;" type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?> aria-label="Add to cart">
                        <i class="bi bi-cart-plus fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </article>
</div>

<style>
    .product-status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
    }
    
    .badge-pill {
        background: rgba(10, 11, 16, 0.6);
        backdrop-filter: blur(8px);
        color: var(--primary);
        font-size: 0.65rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        border: 1px solid rgba(99, 102, 241, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>
