<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<header class="collection-hero">
    <div class="container text-center">
        <div class="status-indicator mb-3">
            <span class="status-dot"></span>
            Live Catalog
        </div>
        <h1 class="display-4 fw-800 text-white text-glow">Elite Collection</h1>
        <p class="text-secondary mx-auto" style="max-width: 600px;">
            Experience the pinnacle of gaming performance with our curated selection of high-end hardware and peripherals.
        </p>
        
        <form action="<?= base_url('/collection') ?>" method="get" class="elite-search-wrapper mt-4">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Search for gear..." autocomplete="off">
            <button class="btn btn-primary-glow" type="submit">Search</button>
        </form>
    </div>
</header>

<section class="container pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="h4 mb-0 text-white">Results</h2>
            <p class="small text-secondary mb-0"><?= count($products) ?> items found</p>
        </div>
    </div>

    <div class="row g-4">
        <?php if ($products === []): ?>
            <div class="col-12">
                <div class="empty-state py-5 text-center">
                    <i class="bi bi-search fs-1 opacity-25 d-block mb-3"></i>
                    <h3 class="h5">No items matched your search</h3>
                    <p class="text-secondary">Try checking your spelling or use more general terms.</p>
                    <a href="<?= base_url('/collection') ?>" class="btn btn-soft btn-sm mt-2">Clear search</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <?= view('products/_card', ['product' => $product]) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
