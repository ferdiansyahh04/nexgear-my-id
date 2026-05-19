<?php
/**
 * Mobile bottom navigation bar.
 *
 * Visible on screens < 992px wide. Provides one-tap access to the four
 * most-used surfaces: Home / Search / Bag / Account.
 */
$cartCount = array_sum(session('cart') ?? []);
$accountHref = session('is_logged_in') ? base_url('/account') : base_url('/login');
?>
<nav class="mobile-bottom-nav" aria-label="Mobile primary">
    <div class="mobile-bottom-nav-inner">
        <a href="<?= base_url('/') ?>" aria-label="Home">
            <i class="bi bi-house"></i>
            <span>Home</span>
        </a>
        <button type="button" data-mb-search-trigger aria-label="Open search">
            <i class="bi bi-search"></i>
            <span>Search</span>
        </button>
        <a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-label="Open bag">
            <i class="bi bi-bag"></i>
            <?php if ($cartCount > 0): ?>
                <span class="mb-badge"><?= esc($cartCount) ?></span>
            <?php endif; ?>
            <span>Bag</span>
        </a>
        <a href="<?= esc($accountHref) ?>" aria-label="Account">
            <i class="bi bi-person"></i>
            <span><?= session('is_logged_in') ? 'Account' : 'Sign In' ?></span>
        </a>
    </div>
</nav>
