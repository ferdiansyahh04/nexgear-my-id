<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <!-- Greeting hero -->
    <div class="px-4 px-lg-5 py-5 border-bottom border-dark">
        <div class="text-uppercase text-muted fw-bold mb-2" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            Welcome Back
        </div>
        <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 700; line-height: 1; letter-spacing: -0.04em; text-transform: uppercase; margin: 0;">
            Hello, <span class="font-serif" style="text-transform: none; font-style: italic;"><?= esc($name ?: 'curator') ?></span>.
        </h1>
        <p class="text-muted font-serif italic mt-3 mb-0" style="font-size: 1rem;">
            <?= esc($email) ?>
        </p>
    </div>

    <!-- Stat cards -->
    <div class="row g-0 border-bottom border-dark">
        <a href="<?= base_url('/account/orders') ?>" class="col-md-3 account-stat-card">
            <span class="account-stat-label">Orders</span>
            <span class="account-stat-value"><?= number_format($totalOrders) ?></span>
            <span class="account-stat-arrow">View History →</span>
        </a>
        <a href="<?= base_url('/account/orders') ?>" class="col-md-3 account-stat-card">
            <span class="account-stat-label">Total Spent</span>
            <span class="account-stat-value">Rp <?= number_format($totalSpent, 0, ',', '.') ?></span>
            <span class="account-stat-arrow">Across all orders →</span>
        </a>
        <a href="<?= base_url('/account/wishlist') ?>" class="col-md-3 account-stat-card">
            <span class="account-stat-label">Wishlist</span>
            <span class="account-stat-value"><?= number_format($wishlistCount) ?></span>
            <span class="account-stat-arrow">Saved items →</span>
        </a>
        <a href="<?= base_url('/account/addresses') ?>" class="col-md-3 account-stat-card">
            <span class="account-stat-label">Addresses</span>
            <span class="account-stat-value"><?= number_format($addressCount) ?></span>
            <span class="account-stat-arrow">Manage book →</span>
        </a>
    </div>

    <div class="row g-0">
        <!-- Recent orders -->
        <div class="col-lg-8 border-end-lg border-dark">
            <div class="px-4 px-lg-5 py-4 border-bottom border-dark d-flex justify-content-between align-items-center">
                <h2 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
                    <span class="font-serif me-2">RC /</span> Recent Orders
                </h2>
                <a href="<?= base_url('/account/orders') ?>" class="account-nav-link" style="font-size: 0.7rem;">
                    All Orders <span>→</span>
                </a>
            </div>

            <?php if ($recentOrders === []): ?>
                <div class="px-4 px-lg-5 py-5 text-center">
                    <p class="font-serif italic text-muted mb-4">You haven't placed any orders yet.</p>
                    <a href="<?= base_url('/collection') ?>" class="btn btn-dark px-4 py-3 rounded-0 text-uppercase fw-bold"
                       style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.8rem;">
                        Start Shopping →
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($recentOrders as $order):
                    $info = $statusMap[$order['status']] ?? ['label' => ucfirst($order['status']), 'tone' => 'muted'];
                ?>
                    <a href="<?= base_url('/account/orders/' . (int) $order['id']) ?>" class="account-recent-row">
                        <div>
                            <div class="account-recent-id">Order #<?= (int) $order['id'] ?></div>
                            <div class="account-recent-date"><?= date('d M Y · H:i', strtotime($order['created_at'])) ?></div>
                        </div>
                        <span class="status-pill status-tone-<?= esc($info['tone']) ?>"><?= esc($info['label']) ?></span>
                        <div class="account-recent-total font-serif italic">
                            Rp <?= number_format((float) $order['total'], 0, ',', '.') ?>
                        </div>
                        <span class="account-recent-arrow">→</span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar: default address + quick actions -->
        <div class="col-lg-4">
            <div class="px-4 px-lg-5 py-4 border-bottom border-dark">
                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
                    Default Address
                </h3>
                <?php if ($defaultAddress): ?>
                    <div style="font-size: 0.95rem; line-height: 1.6;">
                        <strong><?= esc($defaultAddress['name']) ?></strong><br>
                        <span class="text-muted"><?= esc($defaultAddress['phone']) ?></span><br>
                        <?= nl2br(esc($defaultAddress['address'])) ?><br>
                        <?= esc($defaultAddress['city']) ?>, <?= esc($defaultAddress['postal_code']) ?>
                    </div>
                    <a href="<?= base_url('/account/addresses') ?>" class="account-nav-link mt-3 d-inline-flex" style="font-size: 0.65rem;">
                        Manage <span>→</span>
                    </a>
                <?php else: ?>
                    <p class="font-serif italic text-muted">No address saved yet.</p>
                    <a href="<?= base_url('/account/addresses') ?>" class="btn btn-outline-dark px-4 py-2 rounded-0 text-uppercase fw-bold"
                       style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                        + Add Address
                    </a>
                <?php endif; ?>
            </div>

            <div class="px-4 px-lg-5 py-4 border-bottom border-dark">
                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
                    Quick Actions
                </h3>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="<?= base_url('/account/orders') ?>" class="text-decoration-none text-dark text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem; letter-spacing: 0.05em;">→ Order History</a></li>
                    <li class="mb-2"><a href="<?= base_url('/account/wishlist') ?>" class="text-decoration-none text-dark text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem; letter-spacing: 0.05em;">→ Wishlist</a></li>
                    <li class="mb-2"><a href="<?= base_url('/account/addresses') ?>" class="text-decoration-none text-dark text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem; letter-spacing: 0.05em;">→ Address Book</a></li>
                    <li class="mb-2"><a href="<?= base_url('/help') ?>" class="text-decoration-none text-dark text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem; letter-spacing: 0.05em;">→ Help &amp; FAQ</a></li>
                </ul>
            </div>

            <div class="px-4 px-lg-5 py-4">
                <form action="<?= base_url('/logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-dark w-100 py-2 rounded-0 text-uppercase fw-bold"
                            style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
