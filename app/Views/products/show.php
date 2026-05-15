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

<div class="container-fluid px-0 border-bottom border-dark">
    <div class="row g-0">
        <!-- Left Side: Scrolling Images -->
        <div class="col-lg-7 d-flex flex-column">
            <div class="w-100 p-0 p-lg-5 border-bottom border-dark" data-aos="fade-in">
                <img src="<?= $productImage ?>" alt="<?= esc($product['name']) ?>" class="img-fluid w-100" style="object-fit: cover;">
            </div>
            <!-- Additional images to create scroll length -->
            <div class="w-100 p-0 p-lg-5 border-bottom border-dark bg-white">
                <img src="<?= $productImage ?>" alt="<?= esc($product['name']) ?>" class="img-fluid w-100" style="object-fit: cover; filter: grayscale(100%); opacity: 0.5;">
            </div>
            <div class="w-100 p-0 p-lg-5 bg-light">
                <img src="<?= $productImage ?>" alt="<?= esc($product['name']) ?>" class="img-fluid w-100" style="object-fit: cover; transform: scale(0.8) rotate(-5deg);">
            </div>
        </div>
        
        <!-- Right Side: Sticky Product Details -->
        <div class="col-lg-5 border-start border-dark position-relative">
            <div class="sticky-top p-4 p-md-5 d-flex flex-column" style="top: 100px; height: fit-content;">

                <nav aria-label="breadcrumb" class="mb-5">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-muted text-decoration-none text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.1em; font-family: 'Space Grotesk', sans-serif;">Archive</a></li>
                        <li class="breadcrumb-item active text-dark text-uppercase" aria-current="page" style="font-size: 0.65rem; letter-spacing: 0.1em; font-family: 'Space Grotesk', sans-serif; opacity: 0.5;"><?= esc($product['name']) ?></li>
                    </ol>
                </nav>
                
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700; line-height: 1; letter-spacing: -0.04em; color: var(--primary); text-transform: uppercase; margin-bottom: 1.5rem;">
                    <?= esc($product['name']) ?>
                </h1>
                
                <div class="d-flex align-items-end justify-content-between mb-4 pb-4 border-bottom border-dark">
                    <div class="font-serif fs-2" style="font-style: italic;">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></div>
                    <div class="text-uppercase text-muted" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                        <?= esc($product['stock']) ?> units in vault
                    </div>
                </div>
                
                <div class="mb-5">
                    <div class="text-uppercase text-muted small fw-bold mb-3" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Technical Specifications</div>
                    <div class="text-secondary" style="line-height: 1.8; font-size: 1rem;">
                        <?= nl2br(esc($product['description'])) ?>
                    </div>
                </div>
                
                <div class="mt-auto pt-5">
                    <?php if ((int) $product['stock'] < 1): ?>
                        <div class="bg-dark text-white text-center py-3 text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Vault Empty / Sold Out</div>
                    <?php else: ?>
                        <form action="<?= base_url('/cart/add/' . $product['id']) ?>" method="post" class="ajax-add-to-cart">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-4" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">
                                <span class="btn-text">Add to Bag</span>
                                <span class="btn-icon">→</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mt-4 text-uppercase text-muted" style="font-size: 0.6rem; letter-spacing: 0.15em; font-family: 'Space Grotesk', sans-serif; font-weight: 700;">
                        <span>✦ Complimentary Shipping</span>
                        <span>✦ Secure Encryption</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection() ?>
