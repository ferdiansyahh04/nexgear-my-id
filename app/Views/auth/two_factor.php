<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="auth-shell container">
    <div class="auth-panel">
        <div class="text-center mb-4">
            <p class="eyebrow">Two-Factor Authentication</p>
            <h1 class="auth-title">Enter Code</h1>
            <p class="text-muted font-serif italic mt-2 mb-0" style="font-size: 0.95rem;">
                Open your authenticator app and enter the 6-digit code.
            </p>
        </div>

        <form action="<?= base_url('/login/2fa') ?>" method="post" class="vstack gap-3">
            <?= csrf_field() ?>
            <div data-field>
                <label class="form-label text-secondary small fw-bold" for="code">Verification Code</label>
                <input class="form-control vp-input filter-price-input"
                       type="text" id="code" name="code"
                       placeholder="123456"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       maxlength="6"
                       pattern="[0-9]{6}"
                       required autofocus
                       style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.4em; font-size: 1.4rem; text-align: center;">
                <div class="field-error" data-error></div>
            </div>

            <button class="btn btn-dark w-100 py-3 mt-2 rounded-0 text-uppercase fw-bold" type="submit"
                    style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.85rem;">
                Verify <span class="ms-2">→</span>
            </button>
        </form>

        <p class="mt-4 mb-0 text-center text-muted small font-serif italic">
            Lost access to your authenticator? <a href="<?= base_url('/contact') ?>" class="link-accent">Contact support</a>.
        </p>
    </div>
</section>
<?= $this->endSection() ?>
