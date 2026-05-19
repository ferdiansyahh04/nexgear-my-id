<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$f = $filters ?? ['q' => '', 'sort' => 'newest', 'min_price' => null, 'max_price' => null, 'stock' => '', 'category' => 0];
$categories = $categories ?? [];
?>
<!-- Minimalist Collection Header -->
<div class="border-bottom border-dark">
    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center py-3 px-4 px-lg-5">
            <h1 class="h6 mb-0 text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">All Objects</h1>
            <div class="d-flex align-items-center gap-4">
                <span class="small text-muted font-serif italic" id="filterTotalCount"><?= (int) $pager->getTotal() ?> items</span>
                <button type="button" class="btn btn-link text-dark text-decoration-none p-0 small text-uppercase fw-bold filter-toggle"
                        style="font-size: 0.65rem; letter-spacing: 0.1em;"
                        aria-controls="filterPanel"
                        aria-expanded="false">
                    <span class="filter-toggle-text">Filter + Sort</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Category quick chips (B5) -->
<?php if ($categories !== []): ?>
    <div class="border-bottom border-dark px-4 px-lg-5 py-3 d-flex gap-2 align-items-center flex-wrap category-strip">
        <span class="text-uppercase text-muted fw-bold me-2" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">Category</span>
        <button type="button" class="filter-chip <?= (int) $f['category'] === 0 ? 'is-active' : '' ?>"
                data-filter-name="category" data-filter-value="0">All</button>
        <?php foreach ($categories as $cat): ?>
            <button type="button" class="filter-chip <?= (int) $f['category'] === (int) $cat['id'] ? 'is-active' : '' ?>"
                    data-filter-name="category" data-filter-value="<?= (int) $cat['id'] ?>">
                <?= esc($cat['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Filter Panel (collapsible) -->
<div class="filter-panel" id="filterPanel" hidden>
    <form id="filterForm" class="filter-panel-inner">
        <input type="hidden" name="q" value="<?= esc($f['q']) ?>">
        <input type="hidden" name="category" value="<?= (int) $f['category'] ?>">

        <div class="filter-group">
            <label class="filter-label">Sort By</label>
            <div class="filter-options" data-filter-name="sort">
                <?php
                $sortOptions = [
                    'newest'     => 'Newest',
                    'oldest'     => 'Oldest',
                    'price_asc'  => 'Price ↑',
                    'price_desc' => 'Price ↓',
                    'name_asc'   => 'A → Z',
                ];
                foreach ($sortOptions as $value => $label):
                ?>
                    <button type="button" class="filter-chip <?= $f['sort'] === $value ? 'is-active' : '' ?>"
                            data-filter-name="sort" data-filter-value="<?= esc($value) ?>">
                        <?= esc($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="sort" value="<?= esc($f['sort']) ?>">
        </div>

        <div class="filter-group">
            <label class="filter-label">Availability</label>
            <div class="filter-options" data-filter-name="stock">
                <?php
                $stockOptions = ['' => 'All', 'in' => 'In Stock', 'low' => 'Low Stock', 'out' => 'Sold Out'];
                foreach ($stockOptions as $value => $label):
                ?>
                    <button type="button" class="filter-chip <?= $f['stock'] === $value ? 'is-active' : '' ?>"
                            data-filter-name="stock" data-filter-value="<?= esc($value) ?>">
                        <?= esc($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="stock" value="<?= esc($f['stock']) ?>">
        </div>

        <div class="filter-group">
            <label class="filter-label">Price Range (Rp)</label>
            <div class="filter-price-row">
                <input type="number" min="0" step="10000" name="min_price"
                       class="filter-price-input"
                       placeholder="Min"
                       value="<?= $f['min_price'] !== null ? (int) $f['min_price'] : '' ?>">
                <span class="filter-price-divider">→</span>
                <input type="number" min="0" step="10000" name="max_price"
                       class="filter-price-input"
                       placeholder="Max"
                       value="<?= $f['max_price'] !== null ? (int) $f['max_price'] : '' ?>">
            </div>
        </div>

        <div class="filter-actions">
            <button type="reset" class="filter-reset" data-filter-clear>Reset</button>
        </div>
    </form>
</div>

<section class="container-fluid px-0 border-bottom border-dark position-relative" id="productsGridSection">
    <div class="row g-0 border-start border-dark" id="productsGrid">
        <?= view('products/_grid', ['products' => $products]) ?>
    </div>

    <!-- Skeleton overlay (A7) -->
    <div class="grid-skeleton-overlay" id="gridSkeletonOverlay" hidden aria-hidden="true">
        <div class="row g-0 border-start border-dark">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="col-md-6 col-lg-4 border-end border-bottom border-dark">
                    <div class="skel-card">
                        <div class="skel-card-media"></div>
                        <div class="skel-card-body">
                            <div class="skel-line w-75"></div>
                            <div class="skel-line w-50"></div>
                        </div>
                        <div class="skel-card-cta"></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<div class="container-fluid px-4 px-lg-5 py-4 d-flex justify-content-center" id="productsPagerWrap">
    <?php if ($pager->getPageCount() > 1): ?>
        <?= $pager->links('default', 'default_full') ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
