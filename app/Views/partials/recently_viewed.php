<?php
/**
 * Recently Viewed Strip
 *
 * Variables (optional):
 *   $excludeId (int|null) — omit the currently active product
 *   $title     (string)   — custom heading
 */
$excludeId = $excludeId ?? null;
$service   = new \App\Libraries\RecentlyViewedService();
$items     = $service->items($excludeId);

if ($items === []) {
    return;
}

$heading = $title ?? 'Recently Viewed';
?>
<section class="recently-viewed border-top border-bottom border-dark" aria-label="Recently viewed products">
    <div class="recently-viewed-header px-4 px-lg-5 py-4 border-bottom border-dark d-flex justify-content-between align-items-center">
        <h2 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">RV /</span><?= esc($heading) ?>
        </h2>
        <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            <?= count($items) ?> Item<?= count($items) === 1 ? '' : 's' ?>
        </span>
    </div>
    <div class="recently-viewed-track">
        <?php foreach ($items as $product):
            $img = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));
        ?>
            <a class="recently-viewed-card" href="<?= base_url('/products/' . (int) $product['id']) ?>">
                <div class="recently-viewed-thumb">
                    <img src="<?= $img ?>" alt="<?= esc($product['name']) ?>" loading="lazy"
                         onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=400&auto=format&fit=crop'">
                </div>
                <div class="recently-viewed-meta">
                    <div class="recently-viewed-name text-uppercase fw-bold">
                        <?= esc($product['name']) ?>
                    </div>
                    <div class="recently-viewed-price font-serif italic">
                        Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
