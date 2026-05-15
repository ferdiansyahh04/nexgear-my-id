<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="auth-shell container">
    <div class="auth-panel">
        <div class="text-center mb-4">
            <p class="eyebrow">Welcome back</p>
            <h1 class="auth-title">Sign in</h1>
        </div>
        
        <form action="<?= base_url('/login') ?>" method="post" class="vstack gap-3">
            <?= csrf_field() ?>
            <div>
                <label class="form-label text-secondary small fw-bold" for="email">Email Address</label>
                <input class="form-control vp-input" type="email" id="email" name="email" value="<?= old('email') ?>" placeholder="name@example.com" required autofocus>
            </div>
            <div>
                <div class="d-flex justify-content-between">
                    <label class="form-label text-secondary small fw-bold" for="password">Password</label>
                </div>
                <input class="form-control vp-input" type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button class="btn btn-primary-glow w-100 py-3 mt-2" type="submit">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>
        
        <p class="mt-4 mb-0 text-center text-secondary small">
            New to NexGear? <a class="link-accent" href="<?= base_url('/register') ?>">Create an account</a>
        </p>

        <div class="demo-info">
            <p class="small text-secondary mb-2 fw-bold"><i class="bi bi-info-circle me-1"></i> Demo Accounts:</p>
            <div class="d-flex flex-column gap-1 small">
                <code class="text-primary-light">Admin: admin@nexgear.test / password</code>
                <code class="text-primary-light">User: user@nexgear.test / password</code>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
