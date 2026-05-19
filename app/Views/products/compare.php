<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">CMP /</span> Side-by-Side Comparison
        </h1>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                <?= count($products) ?> / 3 selected
            </span>
            <a href="<?= base_url('/collection') ?>" class="or-split-link" style="font-size: 0.7rem;">
                Back to Collection <span>→</span>
            </a>
        </div>
    </div>

    <?php if ($products === []): ?>
        <div class="px-4 px-lg-5 py-5 text-center">
            <p class="font-serif italic text-muted mb-4">No products selected for comparison.</p>
            <p class="text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.1em;">
                Click the compare icon on any product card to add it here.
            </p>
            <a href="<?= base_url('/collection') ?>" class="btn btn-dark mt-4 px-4 py-3 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.8rem;">
                Browse Products →
            </a>
        </div>
    <?php else: ?>
        <div class="compare-grid" style="grid-template-columns: 200px repeat(<?= count($products) ?>, 1fr);">
            <!-- Header row: empty corner + each product card -->
            <div class="compare-corner"></div>
            <?php foreach ($products as $product):
                $img = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));
            ?>
                <div class="compare-product-cell">
                    <button type="button" class="compare-remove" data-compare-remove="<?= (int) $product['id'] ?>"
                            aria-label="Remove from comparison" title="Remove">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <a href="<?= base_url('/products/' . (int) $product['id']) ?>" class="compare-thumb">
                        <img src="<?= $img ?>" alt="<?= esc($product['name']) ?>" loading="lazy"
                             onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=400&auto=format&fit=crop'">
                    </a>
                    <h3 class="compare-product-name">
                        <a href="<?= base_url('/products/' . (int) $product['id']) ?>"><?= esc($product['name']) ?></a>
                    </h3>
                    <?php if ((int) $product['stock'] > 0): ?>
                        <form action="<?= base_url('/cart/add/' . (int) $product['id']) ?>" method="post" class="ajax-add-to-cart compare-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="compare-add-btn">
                                <span class="btn-text">Add to Bag</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="compare-add-btn is-disabled">Sold Out</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Spec rows -->
            <?php foreach ($rows as $label => $values):
                // Highlight differences when all 3 slots filled
                $highlight = [];
                if (count($products) > 1 && in_array($label, ['Price', 'Stock'], true)) {
                    if ($label === 'Price') {
                        $nums = array_map(fn ($p) => (float) $p['price'], $products);
                        $min = min($nums);
                        foreach ($nums as $i => $v) $highlight[$i] = $v === $min ? 'best' : '';
                    } elseif ($label === 'Stock') {
                        $nums = array_map(fn ($p) => (int) $p['stock'], $products);
                        $max = max($nums);
                        foreach ($nums as $i => $v) $highlight[$i] = $v === $max && $v > 0 ? 'best' : '';
                    }
                }
            ?>
                <div class="compare-label"><?= esc($label) ?></div>
                <?php foreach ($values as $i => $value): ?>
                    <div class="compare-cell <?= ($highlight[$i] ?? '') === 'best' ? 'is-best' : '' ?>">
                        <?= esc($value) ?>
                        <?php if (($highlight[$i] ?? '') === 'best'): ?>
                            <span class="compare-best-tag font-serif">★ best</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>
