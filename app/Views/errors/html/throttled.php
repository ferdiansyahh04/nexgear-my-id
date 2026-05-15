<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <h1 class="display-1 fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: -0.04em;">429</h1>
        <p class="font-serif italic fs-4 text-muted mb-4">Too many requests</p>
        <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">You've made too many attempts. Please wait a moment before trying again.</p>
        <a href="<?= base_url('/') ?>" class="btn btn-dark text-uppercase fw-bold px-5 py-3 rounded-0" style="font-size: 0.8rem; letter-spacing: 0.1em;">Return Home</a>
    </div>
</div>

<?= $this->endSection() ?>
