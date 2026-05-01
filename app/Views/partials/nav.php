<?php $cartCount = array_sum(session('cart') ?? []); ?>
<nav class="navbar navbar-expand-lg vp-nav sticky-top" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-1" href="<?= base_url('/') ?>" style="font-family: 'Outfit', sans-serif;">
            <span class="fw-bold text-white fs-3 tracking-tight">Hypernex</span>
            <span class="text-primary fs-5 fw-normal" style="margin-top: -10px;">®</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation" style="border: none; padding: 0;">
            <i class="bi bi-list fs-1 text-white"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 gap-lg-4 mt-3 mt-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/products') ?>">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/collection') ?>">Store</a></li>
                <?php if (session('role') === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin/products') ?>">Dashboard</a></li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="<?= base_url('/cart') ?>" aria-label="Cart">
                        <i class="bi bi-cart3 fs-5 me-2"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-pill"><?= esc($cartCount) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (session('is_logged_in')): ?>
                    <li class="nav-item dropdown">
                        <button class="btn btn-soft dropdown-toggle w-100 w-lg-auto btn-sm" data-bs-toggle="dropdown">
                            <?= esc(session('user_name')) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><span class="dropdown-item-text opacity-50 small text-uppercase fw-bold"><?= esc(session('role')) ?></span></li>
                            <li><hr class="dropdown-divider border-white border-opacity-10"></li>
                            <li>
                                <form action="<?= base_url('/logout') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button class="dropdown-item" type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/login') ?>">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary-glow btn-sm px-4" href="<?= base_url('/register') ?>">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
window.onscroll = function() {
    var nav = document.getElementById('mainNavbar');
    if (window.pageYOffset > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
};
</script>
