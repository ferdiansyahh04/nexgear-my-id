<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container pb-5 pt-4">
    <div class="page-heading mb-5">
        <div>
            <p class="eyebrow">Final Step</p>
            <h1 class="text-white">Checkout</h1>
            <p class="text-secondary">Provide your delivery details to complete the purchase.</p>
        </div>
        <a href="<?= base_url('/cart') ?>" class="btn btn-soft btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Edit Cart
        </a>
    </div>

    <?php if ($items === []): ?>
        <div class="empty-state py-5 text-center">
            <i class="bi bi-cart-x fs-1 opacity-25 mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-secondary">Add some gear to your cart before checking out.</p>
            <a href="<?= base_url('/collection') ?>" class="btn btn-primary-glow mt-3">Browse Gear</a>
        </div>
    <?php else: ?>
        <form action="<?= base_url('/checkout/place') ?>" method="post">
            <?= csrf_field() ?>
            <div class="checkout-grid">
                <div class="checkout-form-section">
                    <div class="vp-card p-4 mb-4">
                        <h2 class="h5 mb-4 text-white d-flex align-items-center">
                            <span class="step-num me-2">1</span> Shipping Information
                        </h2>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label text-secondary small fw-bold">Full Name</label>
                                <input type="text" name="shipping_name" class="form-control vp-input" value="<?= esc(session('user_name')) ?>" required placeholder="Receiver name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">Phone Number</label>
                                <input type="tel" name="shipping_phone" class="form-control vp-input" required placeholder="+62...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small fw-bold">Postal Code</label>
                                <input type="text" name="shipping_postal_code" class="form-control vp-input" required placeholder="12345">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary small fw-bold">Full Address</label>
                                <textarea name="shipping_address" class="form-control vp-input" rows="3" required placeholder="Street name, building, house number..."></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-secondary small fw-bold">City</label>
                                <input type="text" name="shipping_city" class="form-control vp-input" required placeholder="Jakarta, Bandung, etc.">
                            </div>
                        </div>
                    </div>

                    <div class="vp-card p-4">
                        <h2 class="h5 mb-4 text-white d-flex align-items-center">
                            <span class="step-num me-2">2</span> Order Review
                        </h2>
                        <?php foreach ($items as $item): ?>
                            <div class="checkout-line">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-dark rounded p-1" style="width: 50px; height: 50px; overflow: hidden;">
                                        <?php
                                            $name = strtolower($item['product']['name']);
                                            $fallback = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=100&auto=format&fit=crop';
                                            if (strpos($name, 'keyboard') !== false) $fallback = 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=100&auto=format&fit=crop';
                                            elseif (strpos($name, 'mouse') !== false) $fallback = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=100&auto=format&fit=crop';
                                            elseif (strpos($name, 'headset') !== false) $fallback = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=100&auto=format&fit=crop';
                                        ?>
                                        <img src="<?= $fallback ?>" class="w-100 h-100 object-fit-cover rounded" alt="">
                                    </div>
                                    <div>
                                        <span class="d-block fw-bold text-white"><?= esc($item['product']['name']) ?></span>
                                        <span class="text-secondary small">Qty: <?= esc($item['qty']) ?></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="d-block fw-bold text-white">Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="summary-panel vp-card p-4">
                    <h2 class="h5 mb-4 text-white">Payment Details</h2>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal</span>
                        <span class="text-white">Rp <?= number_format((float) $total, 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-secondary">Shipping Cost</span>
                        <span class="text-success fw-bold">FREE</span>
                    </div>
                    
                    <hr class="border-white border-opacity-10 my-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="h6 mb-0 text-white">Grand Total</span>
                        <span class="h4 mb-0 fw-bold text-primary">Rp <?= number_format((float) $total, 0, ',', '.') ?></span>
                    </div>
                    
                    <button class="btn btn-primary-glow w-100 py-3" type="submit">
                        <i class="bi bi-shield-check me-2"></i>Complete Order
                    </button>
                    
                    <p class="text-center text-secondary small mt-4 mb-0">
                        <i class="bi bi-lock-fill me-1"></i> Secure encrypted transaction
                    </p>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>

<style>
    .step-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 800;
    }
</style>
<?= $this->endSection() ?>
