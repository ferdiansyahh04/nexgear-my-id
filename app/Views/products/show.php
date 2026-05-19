<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
/**
 * Build a small gallery from primary + secondary images, then any
 * extra images saved in product_images, with curated Unsplash
 * fallbacks when the product still uses the SVG default.
 */
function nexgear_show_gallery(array $product, array $extras = []): array
{
    $name      = strtolower($product['name']);
    $primary   = $product['image'] && $product['image'] !== 'default-product.svg'
        ? base_url('uploads/products/' . esc($product['image']))
        : null;
    $secondary = !empty($product['image_secondary'])
        ? base_url('uploads/products/' . esc($product['image_secondary']))
        : null;

    if (!$primary) {
        if (str_contains($name, 'keyboard')) {
            $primary   = 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop';
            $secondary = $secondary ?? 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?q=80&w=1200&auto=format&fit=crop';
        } elseif (str_contains($name, 'mouse')) {
            $primary   = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=1200&auto=format&fit=crop';
            $secondary = $secondary ?? 'https://images.unsplash.com/photo-1617325247661-6750456102d9?q=80&w=1200&auto=format&fit=crop';
        } elseif (str_contains($name, 'headset') || str_contains($name, 'audio')) {
            $primary   = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=1200&auto=format&fit=crop';
            $secondary = $secondary ?? 'https://images.unsplash.com/photo-1484704849700-f032a568e944?q=80&w=1200&auto=format&fit=crop';
        } elseif (str_contains($name, 'mic') || str_contains($name, 'stream')) {
            $primary   = 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?q=80&w=1200&auto=format&fit=crop';
            $secondary = $secondary ?? 'https://images.unsplash.com/photo-1583394838336-acd977730f8a?q=80&w=1200&auto=format&fit=crop';
        } elseif (str_contains($name, 'pad') || str_contains($name, 'mat')) {
            $primary   = 'https://images.unsplash.com/photo-1614149162883-504ce4d13909?q=80&w=1200&auto=format&fit=crop';
            $secondary = $secondary ?? 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=1200&auto=format&fit=crop';
        } else {
            $primary   = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=1200&auto=format&fit=crop';
            $secondary = $secondary ?? 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=1200&auto=format&fit=crop';
        }
    }

    $gallery = [$primary];
    if ($secondary) $gallery[] = $secondary;
    foreach ($extras as $img) {
        $gallery[] = base_url('uploads/products/' . esc($img['path']));
    }

    // Pad to at least 4 thumbnails for a fuller layout
    if (count($gallery) < 4) {
        $detailShots = [
            'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1541140532154-b024d705b90a?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=800&auto=format&fit=crop',
        ];
        foreach ($detailShots as $shot) {
            if (count($gallery) >= 4) break;
            if (! in_array($shot, $gallery, true)) $gallery[] = $shot;
        }
    }

    return array_values(array_unique($gallery));
}

$gallery     = nexgear_show_gallery($product, $extraImages ?? []);
$inStock     = (int) $product['stock'] >= 1;
$average     = $aggregate['average'] ?? 0;
$reviewCount = $aggregate['count'] ?? 0;
$breakdown   = $aggregate['breakdown'] ?? [1=>0,2=>0,3=>0,4=>0,5=>0];
?>

<div class="container-fluid px-0 border-bottom border-dark">
    <div class="row g-0">
        <!-- Left: Gallery -->
        <div class="col-lg-7 p-4 p-lg-5 border-end-lg border-dark" data-aos="fade-in">
            <div class="gallery-stage" id="galleryStage" aria-label="Product image, hover or click to zoom">
                <img id="galleryStageImage" src="<?= esc($gallery[0]) ?>" alt="<?= esc($product['name']) ?>">
            </div>
            <div class="gallery-thumbs" id="galleryThumbs">
                <?php foreach ($gallery as $i => $src): ?>
                    <button type="button" class="gallery-thumb <?= $i === 0 ? 'is-active' : '' ?>"
                            data-full="<?= esc($src) ?>"
                            aria-label="Show image <?= $i + 1 ?>">
                        <img src="<?= esc($src) ?>" alt="<?= esc($product['name']) ?> view <?= $i + 1 ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right: Sticky Product Details -->
        <div class="col-lg-5 border-start border-dark position-relative">
            <div class="sticky-top p-4 p-md-5 d-flex flex-column" style="top: 100px; height: fit-content;">

                <nav aria-label="breadcrumb" class="mb-5">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-muted text-decoration-none text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.1em; font-family: 'Space Grotesk', sans-serif;">Archive</a></li>
                        <?php if (! empty($category)): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('/products?category=' . (int) $category['id']) ?>" class="text-muted text-decoration-none text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.1em; font-family: 'Space Grotesk', sans-serif;">
                                    <?= esc($category['name']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active text-dark text-uppercase" aria-current="page" style="font-size: 0.65rem; letter-spacing: 0.1em; font-family: 'Space Grotesk', sans-serif; opacity: 0.5;"><?= esc($product['name']) ?></li>
                    </ol>
                </nav>
                
                <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700; line-height: 1; letter-spacing: -0.04em; color: var(--primary); text-transform: uppercase; margin-bottom: 1.5rem;">
                    <?= esc($product['name']) ?>
                </h1>

                <?php if ($reviewCount > 0): ?>
                    <a href="#reviews" class="d-flex align-items-center gap-2 mb-3 text-decoration-none text-dark">
                        <span class="rating-stars" aria-label="<?= esc($average) ?> out of 5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?= $i <= round($average) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <span class="font-serif italic" style="font-size: 0.95rem;">
                            <?= number_format($average, 1) ?> <span class="text-muted">/ <?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></span>
                        </span>
                    </a>
                <?php endif; ?>
                
                <div class="d-flex align-items-end justify-content-between mb-4 pb-4 border-bottom border-dark">
                    <div class="font-serif fs-2" style="font-style: italic;">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></div>
                    <div class="text-uppercase text-muted text-end" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                        <span class="d-block live-stock"
                              data-stock="<?= (int) $product['stock'] ?>"
                              data-product-id="<?= (int) $product['id'] ?>">
                            <span class="live-stock-dot" aria-hidden="true"></span>
                            <span class="live-stock-text"><?= (int) $product['stock'] ?> units in vault</span>
                        </span>
                        <span class="d-block live-viewers mt-1" data-viewers
                              style="font-size: 0.65rem; letter-spacing: 0.12em; opacity: 0.6;">
                            <span data-viewers-count></span> viewing now
                        </span>
                    </div>
                </div>
                
                <div class="mb-5">
                    <div class="text-uppercase text-muted small fw-bold mb-3" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Technical Specifications</div>
                    <div class="text-secondary" style="line-height: 1.8; font-size: 1rem;">
                        <?= nl2br(esc($product['description'])) ?>
                    </div>
                </div>
                
                <div class="mt-auto pt-5">
                    <?php if (! $inStock): ?>
                        <div class="bg-dark text-white text-center py-3 text-uppercase fw-bold mb-3" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Vault Empty / Sold Out</div>

                        <!-- B8 — Notify when back in stock -->
                        <form action="<?= base_url('/products/' . (int) $product['id'] . '/stock-alert') ?>" method="post" class="stock-alert-form" data-stock-alert>
                            <?= csrf_field() ?>
                            <div class="text-uppercase fw-bold mb-2" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.15em; color: var(--text-muted);">
                                Notify When Available
                            </div>
                            <div class="d-flex gap-2">
                                <input type="email" name="email" class="filter-price-input flex-grow-1" required
                                       value="<?= esc(session('user_email') ?? '') ?>" placeholder="your@email.com">
                                <button type="submit" class="btn btn-outline-dark px-4 rounded-0 text-uppercase fw-bold"
                                        style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                                    Notify Me
                                </button>
                            </div>
                            <div class="text-muted font-serif italic small mt-2" data-stock-alert-hint>
                                We'll email you the moment it returns to the vault.
                            </div>
                        </form>
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

<!-- B4 — Reviews section -->
<section class="container-fluid px-0 border-bottom border-dark" id="reviews">
    <div class="px-4 px-lg-5 py-4 border-bottom border-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">RV /</span> Reviews
        </h2>
        <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            <?= $reviewCount ?> Total
        </span>
    </div>

    <div class="row g-0">
        <!-- Aggregate panel -->
        <div class="col-lg-4 border-end-lg border-dark p-4 p-lg-5">
            <?php if ($reviewCount > 0): ?>
                <div class="rating-aggregate">
                    <div class="rating-aggregate-score"><?= number_format($average, 1) ?></div>
                    <div class="rating-stars rating-stars-lg" aria-label="<?= esc($average) ?> out of 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= round($average) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="text-uppercase text-muted fw-bold mt-2" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.12em;">
                        Based on <?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?>
                    </div>
                </div>

                <div class="rating-breakdown mt-4">
                    <?php for ($i = 5; $i >= 1; $i--):
                        $count = (int) $breakdown[$i];
                        $pct = $reviewCount > 0 ? round(($count / $reviewCount) * 100) : 0;
                    ?>
                        <div class="rating-breakdown-row">
                            <span class="rating-breakdown-label"><?= $i ?> ★</span>
                            <div class="rating-breakdown-bar">
                                <div class="rating-breakdown-fill" style="width: <?= $pct ?>%;"></div>
                            </div>
                            <span class="rating-breakdown-count"><?= $count ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <p class="font-serif italic text-muted">No reviews yet. Be the first to share your thoughts.</p>
            <?php endif; ?>

            <!-- Review form -->
            <?php if (session('is_logged_in') && ($canReview || $userReview)): ?>
                <form action="<?= base_url('/products/' . (int) $product['id'] . '/reviews') ?>" method="post" class="mt-5 review-form">
                    <?= csrf_field() ?>
                    <h3 class="text-uppercase fw-bold mb-3" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                        <?= $userReview ? 'Update Your Review' : 'Write a Review' ?>
                    </h3>
                    <div class="rating-input mb-3" data-rating-input role="radiogroup" aria-label="Rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="rating-input-star">
                                <input type="radio" name="rating" value="<?= $i ?>" <?= ($userReview && (int) $userReview['rating'] === $i) ? 'checked' : '' ?> required>
                                <i class="bi bi-star"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <input type="text" name="title" class="form-control review-input mb-2" placeholder="Headline (optional)"
                           value="<?= esc($userReview['title'] ?? '') ?>" maxlength="160">
                    <textarea name="body" class="form-control review-input" rows="4" placeholder="Share your experience…" maxlength="1500"><?= esc($userReview['body'] ?? '') ?></textarea>
                    <button type="submit" class="btn btn-dark w-100 mt-3 py-2 rounded-0 text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.1em;">
                        <?= $userReview ? 'Update Review' : 'Submit Review' ?>
                    </button>
                </form>
            <?php elseif (session('is_logged_in') && ! $canReview && ! $userReview): ?>
                <div class="mt-5 p-3 border border-dark border-opacity-25" style="font-size: 0.85rem; line-height: 1.5;">
                    <span class="font-serif italic text-muted">
                        Only verified buyers can leave a review. Purchase this product first to share your thoughts.
                    </span>
                </div>
            <?php elseif (! session('is_logged_in')): ?>
                <div class="mt-5">
                    <a href="<?= base_url('/login') ?>" class="btn btn-outline-dark w-100 py-2 rounded-0 text-uppercase fw-bold"
                       style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.1em;">
                        Sign in to leave a review
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reviews list -->
        <div class="col-lg-8 p-4 p-lg-5">
            <?php if ($reviews === []): ?>
                <div class="text-center py-5">
                    <p class="font-serif italic text-muted">No reviews to show.</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <article class="review-item">
                        <header class="review-item-header">
                            <span class="rating-stars" aria-label="<?= (int) $review['rating'] ?> out of 5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= $i <= (int) $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <?php if ((int) ($review['verified_purchase'] ?? 0) === 1): ?>
                                <span class="verified-badge">
                                    <i class="bi bi-patch-check-fill"></i> Verified Purchase
                                </span>
                            <?php endif; ?>
                        </header>
                        <?php if (! empty($review['title'])): ?>
                            <h4 class="review-item-title"><?= esc($review['title']) ?></h4>
                        <?php endif; ?>
                        <?php if (! empty($review['body'])): ?>
                            <p class="review-item-body"><?= nl2br(esc($review['body'])) ?></p>
                        <?php endif; ?>
                        <footer class="review-item-footer">
                            <span><?= esc($review['author_name'] ?? 'Anonymous') ?></span>
                            <span class="font-serif italic">·</span>
                            <span><?= date('d M Y', strtotime($review['created_at'])) ?></span>

                            <?php if (session('is_logged_in') && ((int) $review['user_id'] === (int) session('user_id') || session('role') === 'admin')): ?>
                                <form action="<?= base_url('/reviews/' . (int) $review['id'] . '/delete') ?>" method="post" class="ms-auto" onsubmit="return confirm('Delete this review?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em; text-transform: uppercase; color: #b00020;">
                                        Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= view('partials/recommendations', ['items' => $recommendations ?? [], 'title' => 'You May Also Like']) ?>
<?= view('partials/recently_viewed', ['excludeId' => (int) $product['id'], 'title' => 'Also Recently Viewed']) ?>
<?= $this->endSection() ?>
