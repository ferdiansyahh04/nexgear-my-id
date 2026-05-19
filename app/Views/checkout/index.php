<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">CO /</span> Checkout
        </h1>
        <a href="<?= base_url('/cart') ?>" class="account-nav-link" style="font-size: 0.7rem;">
            ← Edit Cart
        </a>
    </div>

    <?php if ($items === []): ?>
        <div class="px-4 px-lg-5 py-5 text-center">
            <p class="font-serif italic text-muted mb-4">Your cart is empty.</p>
            <a href="<?= base_url('/collection') ?>" class="btn btn-dark px-4 py-3 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.8rem;">
                Browse Products →
            </a>
        </div>
    <?php else: ?>
        <form action="<?= base_url('/checkout/place') ?>" method="post" data-validate novalidate>
            <?= csrf_field() ?>
            <div class="row g-0">
                <!-- Shipping form -->
                <div class="col-lg-8 border-end-lg border-dark p-4 p-lg-5">
                    <!-- B9 — Saved addresses -->
                    <?php if (! empty($addresses)): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="text-uppercase fw-bold mb-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.12em;">
                                    Saved Addresses
                                </label>
                                <a href="<?= base_url('/account/addresses') ?>" target="_blank" class="account-nav-link" style="font-size: 0.65rem;">
                                    Manage <span>→</span>
                                </a>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php foreach ($addresses as $addr): ?>
                                    <button type="button" class="filter-chip address-chip <?= (int) $addr['is_default'] === 1 ? 'is-active' : '' ?>"
                                            data-address-id="<?= (int) $addr['id'] ?>">
                                        <?= esc($addr['label'] ?: $addr['name']) ?>
                                        <?php if ((int) $addr['is_default'] === 1): ?>
                                            <span class="font-serif italic ms-2" style="font-size: 0.7em;">default</span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h2 class="text-uppercase fw-bold mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                        Shipping Information
                    </h2>
                    <div class="row g-3">
                        <div class="col-md-12" data-field>
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Full Name</label>
                            <input type="text" name="shipping_name" class="filter-price-input w-100" value="<?= esc($addresses[0]['name'] ?? session('user_name')) ?>" required placeholder="Receiver name" data-rule="min:3">
                            <div class="field-error" data-error></div>
                        </div>
                        <div class="col-md-6" data-field>
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Phone</label>
                            <input type="tel" name="shipping_phone" class="filter-price-input w-100" required placeholder="+62..."
                                   value="<?= esc($addresses[0]['phone'] ?? '') ?>" data-rule="phone">
                            <div class="field-error" data-error></div>
                        </div>
                        <div class="col-md-6" data-field>
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Postal Code</label>
                            <input type="text" name="shipping_postal_code" class="filter-price-input w-100" required placeholder="12345"
                                   value="<?= esc($addresses[0]['postal_code'] ?? '') ?>" data-rule="postal">
                            <div class="field-error" data-error></div>
                        </div>
                        <div class="col-md-12" data-field>
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Address</label>
                            <textarea name="shipping_address" class="filter-price-input w-100" rows="3" required placeholder="Street name, building, house number..." data-rule="min:10"><?= esc($addresses[0]['address'] ?? '') ?></textarea>
                            <div class="field-error" data-error></div>
                        </div>
                        <div class="col-md-12" data-field>
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">City</label>
                            <input type="text" name="shipping_city" class="filter-price-input w-100" required placeholder="Jakarta, Bandung, etc."
                                   value="<?= esc($addresses[0]['city'] ?? '') ?>" data-rule="min:2">
                            <div class="field-error" data-error></div>
                        </div>
                    </div>

                    <h2 class="text-uppercase fw-bold mt-5 mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                        Order Review
                    </h2>
                    <?php foreach ($items as $item):
                        $img = base_url('uploads/products/' . esc($item['product']['image'] ?: 'default-product.svg'));
                    ?>
                        <div class="order-item-row">
                            <div class="order-item-thumb">
                                <img src="<?= $img ?>" alt="">
                            </div>
                            <div class="order-item-info">
                                <div class="order-item-name"><?= esc($item['product']['name']) ?></div>
                                <div class="order-item-meta">
                                    Qty: <?= (int) $item['qty'] ?>
                                    · Unit: Rp <?= number_format((float) $item['product']['price'], 0, ',', '.') ?>
                                </div>
                            </div>
                            <div class="order-item-subtotal font-serif italic">
                                Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Summary -->
                <div class="col-lg-4 border-start-lg border-dark">
                    <div class="p-4 p-lg-5" style="position: sticky; top: 100px;">
                        <h2 class="text-uppercase fw-bold mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                            Payment Details
                        </h2>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="font-serif italic">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color: #059669;">Discount<?= $coupon ? ' (' . esc($coupon) . ')' : '' ?></span>
                                <span class="font-serif italic" style="color: #059669;">− Rp <?= number_format($discount, 0, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-muted">Shipping</span>
                            <span class="text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.12em; color: #059669;">FREE</span>
                        </div>
                        <hr class="border-dark border-opacity-25 my-3">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="h6 mb-0 text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Total</span>
                            <span class="h3 mb-0 font-serif italic">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>

                        <button class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-4" type="submit"
                                style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.85rem;">
                            <span><i class="bi bi-shield-check me-2"></i>Place Order</span>
                            <span>→</span>
                        </button>

                        <p class="text-center text-muted small mt-4 mb-0 font-serif italic">
                            <i class="bi bi-lock-fill me-1"></i> Secure encrypted transaction
                        </p>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
