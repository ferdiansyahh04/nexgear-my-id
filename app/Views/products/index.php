<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Minimalist Collection Header -->
<div class="border-bottom border-dark">
    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center py-3 px-4 px-lg-5">
            <h1 class="h6 mb-0 text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">All Objects</h1>
            <div class="d-flex align-items-center gap-4">
                <span class="small text-muted font-serif italic"><?= $pager->getTotal() ?> items</span>
                <button class="btn btn-link text-dark text-decoration-none p-0 small text-uppercase fw-bold d-none d-lg-block" style="font-size: 0.65rem; letter-spacing: 0.1em;">Filter + Sort</button>
            </div>
        </div>
    </div>
</div>

<section class="container-fluid px-0 border-bottom border-dark">
    <div class="row g-0 border-start border-dark">
        <?php if ($products === []): ?>
            <div class="col-12 py-5 text-center">
                <p class="font-serif italic text-muted">No objects found in this archive.</p>
                <a href="<?= base_url('/collection') ?>" class="or-split-link justify-content-center">Clear Filters <span>→</span></a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4 border-end border-bottom border-dark">
                    <?= view('products/_card', ['product' => $product]) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php if ($pager->getPageCount() > 1): ?>
<div class="container-fluid px-4 px-lg-5 py-4 d-flex justify-content-center">
    <?= $pager->links('default', 'default_full') ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
