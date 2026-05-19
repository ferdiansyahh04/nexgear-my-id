<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="auth-shell container">
    <div class="auth-panel">
        <div class="text-center mb-4">
            <p class="eyebrow">New player</p>
            <h1 class="auth-title">Join the Store</h1>
        </div>

        <form action="<?= base_url('/register') ?>" method="post" class="vstack gap-3" data-validate novalidate>
            <?= csrf_field() ?>
            <div data-field>
                <label class="form-label text-secondary small fw-bold" for="name">Full Name</label>
                <input class="form-control vp-input" type="text" id="name" name="name" value="<?= old('name') ?>" placeholder="John Doe" required
                       data-rule="min:3">
                <div class="field-error" data-error></div>
            </div>
            <div data-field>
                <label class="form-label text-secondary small fw-bold" for="email">Email Address</label>
                <input class="form-control vp-input" type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="name@example.com" required
                       data-rule="email">
                <div class="field-error" data-error></div>
            </div>
            <div class="row g-2">
                <div class="col-6" data-field>
                    <label class="form-label text-secondary small fw-bold" for="password">Password</label>
                    <input class="form-control vp-input" type="password" id="password" name="password" placeholder="••••••••" minlength="8" required
                           data-rule="password">
                    <div class="password-strength" data-strength hidden>
                        <div class="password-strength-bar" data-strength-bar></div>
                        <span class="password-strength-label" data-strength-label>Too short</span>
                    </div>
                    <div class="field-error" data-error></div>
                </div>
                <div class="col-6" data-field>
                    <label class="form-label text-secondary small fw-bold" for="password_confirm">Confirm</label>
                    <input class="form-control vp-input" type="password" id="password_confirm" name="password_confirm" placeholder="••••••••" minlength="8" required
                           data-rule="match:password">
                    <div class="field-error" data-error></div>
                </div>
            </div>
            
            <button class="btn btn-primary-glow w-100 py-3 mt-2" type="submit">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>
        </form>
        
        <p class="mt-4 mb-0 text-center text-secondary small">
            Already have an account? <a class="link-accent" href="<?= base_url('/login') ?>">Sign in</a>
        </p>
    </div>
</section>
<?= $this->endSection() ?>
