<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">PY /</span> Complete Payment
        </h1>
        <a href="<?= base_url('/account/orders/' . (int) $order['id']) ?>" class="account-nav-link" style="font-size: 0.7rem;">
            View Order →
        </a>
    </div>

    <div class="px-4 px-lg-5 py-5" style="max-width: 640px;">
        <div class="mb-4">
            <span class="text-uppercase text-muted fw-bold d-block mb-2" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                Order #<?= (int) $order['id'] ?>
            </span>
            <h2 class="mb-1" style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight: 700; letter-spacing: -0.03em; text-transform: uppercase;">
                Rp <?= number_format((float) $order['total'], 0, ',', '.') ?>
            </h2>
            <p class="text-muted font-serif italic mb-0">Secure payment powered by Duitku.</p>
        </div>

        <!-- Plain form POST → server creates the invoice and redirects to
             Duitku's hosted payment page. No JS/popup needed. -->
        <form action="<?= base_url('/payment/start/' . (int) $order['id']) ?>" method="post" class="m-0">
            <?= csrf_field() ?>
            <button type="submit"
                    class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-4"
                    style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.85rem;">
                <span><i class="bi bi-credit-card me-2"></i>Pay Now</span>
                <span>→</span>
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0 font-serif italic">
            <i class="bi bi-lock-fill me-1"></i> Pay with bank transfer, e-wallet, QRIS, retail, or card.
        </p>
    </div>
</section>
<?= $this->endSection() ?>
