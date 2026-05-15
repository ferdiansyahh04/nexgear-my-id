<?php $cartCount = array_sum(session('cart') ?? []); ?>
<nav class="navbar navbar-expand-lg vp-nav sticky-top py-4" id="mainNavbar">
    <div class="container-fluid px-4 px-lg-5 d-flex justify-content-between align-items-center">
        
        <!-- Left: Logo -->
        <div class="nav-left" style="flex: 1;">
            <a class="navbar-brand m-0" href="<?= base_url('/') ?>" style="font-family: 'Space Grotesk', sans-serif;">
                <span class="fw-bold text-dark fs-4 tracking-tight">NEXGEAR</span>
            </a>
        </div>
        
        <!-- Center: Menu Toggle -->
        <div class="nav-center text-center d-none d-lg-block" style="flex: 1;">
            <button id="menuToggleText" class="btn btn-link text-dark text-decoration-none text-uppercase fw-bold p-0 d-flex align-items-center justify-content-center mx-auto menu-toggle-premium" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.85rem;">
                <span class="menu-text me-3">Menu</span>
                <div class="burger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
        </div>
        
        <!-- Mobile Toggle (Right aligned on mobile) -->
        <button class="navbar-toggler border-0 p-0 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-2 text-dark"></i>
        </button>
        
        <!-- Right: Actions -->
        <div class="nav-right d-none d-lg-flex justify-content-end align-items-center gap-4" style="flex: 1;">
            <a href="#" class="nav-link text-dark fw-bold d-flex align-items-center" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem; letter-spacing: 0.1em;">
                BAG <span class="ms-1">(<?= esc($cartCount) ?>)</span>
            </a>
        </div>
    </div>
    
    <!-- Collapsible Menu Content -->
    <div class="collapse position-absolute w-100 start-0 border-bottom border-dark" id="mainNav" style="top: 100%; z-index: 1000; background: var(--bg-dark) !important;">
        <div class="container-fluid px-4 px-lg-5 py-5 border-top border-dark">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-uppercase text-muted mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.1em;">Shop</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3"><a href="<?= base_url('/') ?>" class="text-dark text-decoration-none fs-3 fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif;">Home</a></li>
                        <li class="mb-3"><a href="<?= base_url('/collection') ?>" class="text-dark text-decoration-none fs-3 fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif;">Our Shop</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-uppercase text-muted mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.1em;">Account</h5>
                    <ul class="list-unstyled">
                        <?php if (session('is_logged_in')): ?>
                            <li class="mb-2"><span class="text-dark opacity-50 fw-bold small text-uppercase"><?= esc(session('user_email')) ?></span></li>
                            <?php if (session('role') === 'admin'): ?>
                                <li class="mb-3"><a href="<?= base_url('/admin/products') ?>" class="text-dark text-decoration-none fs-5 fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif;">Admin Portal</a></li>
                            <?php endif; ?>
                            <li class="mb-3">
                                <form action="<?= base_url('/logout') ?>" method="post" class="m-0">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-link p-0 text-dark text-decoration-none fs-5 fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif;">Logout</button>
                                </form>
                            </li>
                        <?php else: ?>
                            <li class="mb-3"><a href="<?= base_url('/login') ?>" class="text-dark text-decoration-none fs-5 fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif;">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<?= view('partials/offcanvas_cart', ['cartData' => (new \App\Libraries\CartService())->items()]) ?>

<style>
.vp-nav {
    transition: background-color 0s, padding 0.3s ease;
}
.vp-nav.scrolled {
    background-color: var(--bg-dark) !important;
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
    border-bottom: 1px solid var(--border) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var nav = document.getElementById('mainNavbar');
    var menuToggleBtn = document.getElementById('menuToggleText');
    var mainNav = document.getElementById('mainNav');

    window.onscroll = function() {
        if (window.pageYOffset > 20) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    };

    if (mainNav && menuToggleBtn) {
        mainNav.addEventListener('show.bs.collapse', function () {
            menuToggleBtn.classList.add('active');
            const menuText = menuToggleBtn.querySelector('.menu-text');
            if (menuText) menuText.innerText = 'Close';
        });
        
        mainNav.addEventListener('hide.bs.collapse', function () {
            menuToggleBtn.classList.remove('active');
            const menuText = menuToggleBtn.querySelector('.menu-text');
            if (menuText) menuText.innerText = 'Menu';
        });
    }
});
</script>

