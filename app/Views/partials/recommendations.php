<?php
/**
 * Recommendations partial — used on the product detail page.
 *
 * Variables:
 *   $items (array) — hydrated product rows
 *   $title (string) — heading copy
 */
if (empty($items)) return;
$title = $title ?? 'You May Also Like';
?>
<section class="border-bottom border-dark recently-viewed" aria-label="Recommended products">
    <div class="recently-viewed-header px-4 px-lg-5 py-4 border-bottom border-dark d-flex justify-content-between align-items-center">
        <h2 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">RX /</span><?= esc($title) ?>
        </h2>
        <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            Curated For You
        </span>
    </div>
    <div class="row g-0 border-start border-dark">
        <?php foreach ($items as $product): ?>
            <div class="col-md-6 col-lg-3 border-end border-bottom border-dark">
                <?= view('products/_card', ['product' => $product]) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
