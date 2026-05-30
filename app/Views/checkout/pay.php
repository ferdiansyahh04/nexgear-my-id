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
            <p class="text-muted font-serif italic mb-0">Secure payment powered by Midtrans.</p>
        </div>

        <button type="button" id="payNowBtn"
                class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-4"
                data-order-id="<?= (int) $order['id'] ?>"
                style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.85rem;">
            <span><i class="bi bi-credit-card me-2"></i>Pay Now</span>
            <span>→</span>
        </button>

        <p class="text-center text-muted small mt-4 mb-0 font-serif italic" id="payHint">
            <i class="bi bi-lock-fill me-1"></i> You can pay with bank transfer, e-wallet, QRIS, or card.
        </p>
    </div>
</section>

<!-- Midtrans Snap loader — client key is public by design -->
<script src="<?= esc($snapJsUrl, 'attr') ?>" data-client-key="<?= esc($clientKey, 'attr') ?>"></script>
<script {csp-script-nonce}>
(function () {
    var btn = document.getElementById('payNowBtn');
    var hint = document.getElementById('payHint');
    if (!btn) return;

    var csrfMeta = document.getElementById('csrf-token');
    var csrfName = csrfMeta ? csrfMeta.getAttribute('name') : null;
    var csrfHash = csrfMeta ? csrfMeta.getAttribute('content') : null;

    btn.addEventListener('click', function () {
        var orderId = btn.getAttribute('data-order-id');
        btn.disabled = true;
        hint.textContent = 'Preparing secure payment…';

        var form = new FormData();
        if (csrfName && csrfHash) form.append(csrfName, csrfHash);

        fetch('<?= base_url('/payment/snap') ?>/' + orderId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: form
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === 'already_paid') {
                window.location.href = '<?= base_url('/account/orders') ?>/' + orderId;
                return;
            }
            if (data.status !== 'success' || !data.token || !window.snap) {
                throw new Error(data.message || 'Could not start payment.');
            }
            window.snap.pay(data.token, {
                onSuccess: function () { window.location.href = '<?= base_url('/payment/finish') ?>?order=' + orderId; },
                onPending: function () { window.location.href = '<?= base_url('/account/orders') ?>/' + orderId; },
                onError:   function () { window.location.href = '<?= base_url('/payment/error') ?>?order=' + orderId; },
                onClose:   function () {
                    btn.disabled = false;
                    hint.textContent = 'Payment window closed. You can try again anytime.';
                }
            });
        })
        .catch(function (err) {
            btn.disabled = false;
            hint.textContent = err.message || 'Something went wrong. Please try again.';
        });
    });
})();
</script>
<?= $this->endSection() ?>
