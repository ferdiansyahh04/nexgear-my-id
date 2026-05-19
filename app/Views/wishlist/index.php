<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">WL /</span> Your Wishlist
        </h1>
        <?php if (session('is_logged_in')): ?>
            <a href="<?= base_url('/account/orders') ?>" class="account-nav-link">
                Order History <span>→</span>
            </a>
        <?php endif; ?>
    </div>

    <?php if ($items === []): ?>
        <div class="px-4 px-lg-5 py-5 text-center">
            <p class="font-serif italic text-muted mb-4">No saved items yet.</p>
            <p class="text-uppercase fw-bold mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.15em;">
                Tap the heart icon on any product to save it here.
            </p>
            <a href="<?= base_url('/collection') ?>" class="btn btn-dark px-4 py-3 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.8rem;">
                Browse Products →
            </a>
        </div>
    <?php else: ?>
        <div class="row g-0 border-start border-dark">
            <?php foreach ($items as $product): ?>
                <div class="col-md-6 col-lg-4 border-end border-bottom border-dark">
                    <?= view('products/_card', ['product' => $product]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
