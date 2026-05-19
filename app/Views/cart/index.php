<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">CT /</span> Your Cart
        </h1>
        <a href="<?= base_url('/products') ?>" class="account-nav-link" style="font-size: 0.7rem;">
            ← Continue Shopping
        </a>
    </div>

    <?php if ($items === []): ?>
        <div class="px-4 px-lg-5 py-5 text-center">
            <p class="font-serif italic text-muted mb-4">Your cart is currently empty.</p>
            <a href="<?= base_url('/collection') ?>" class="btn btn-dark px-4 py-3 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.8rem;">
                Browse Products →
            </a>
        </div>
    <?php else: ?>
        <div class="row g-0">
            <div class="col-lg-8 border-end-lg border-dark">
                <form action="<?= base_url('/cart/update') ?>" method="post" class="p-4 p-lg-5">
                    <?= csrf_field() ?>
                    <?php foreach ($items as $item):
                        $product = $item['product'];
                        $img = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));
                    ?>
                        <div class="cart-page-row">
                            <div class="cart-page-thumb">
                                <img src="<?= $img ?>" alt="<?= esc($product['name']) ?>"
                                     onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=200&auto=format&fit=crop'">
                            </div>
                            <div class="cart-page-info">
                                <div class="cart-page-name">
                                    <a href="<?= base_url('/products/' . (int) $product['id']) ?>"><?= esc($product['name']) ?></a>
                                </div>
                                <div class="cart-page-price font-serif italic">
                                    Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
                                </div>
                            </div>
                            <div class="cart-page-qty">
                                <input type="number" min="1" max="<?= (int) $product['stock'] ?>"
                                       name="qty[<?= (int) $product['id'] ?>]" value="<?= (int) $item['qty'] ?>"
                                       class="filter-price-input">
                            </div>
                            <div class="cart-page-subtotal font-serif italic">
                                Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?>
                            </div>
                            <button type="button" class="cart-page-remove remove-item" data-id="<?= (int) $product['id'] ?>" aria-label="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-outline-dark px-4 py-2 rounded-0 text-uppercase fw-bold"
                                style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                            Update Quantities
                        </button>
                        <button type="submit" formaction="<?= base_url('/cart/clear') ?>" class="btn btn-link text-decoration-none text-uppercase fw-bold p-2"
                                style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;"
                                onclick="return confirm('Empty your cart?')">
                            Clear Cart
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4 border-start-lg border-dark">
                <div class="p-4 p-lg-5">
                    <h2 class="text-uppercase fw-bold mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                        Summary
                    </h2>

                    <!-- Coupon form (B7) -->
                    <div class="mb-4 coupon-block" id="couponBlock"
                         data-applied="<?= $coupon ? esc($coupon) : '' ?>"
                         data-discount="<?= number_format($discount, 0, '.', '') ?>"
                         data-subtotal="<?= number_format($subtotal, 0, '.', '') ?>"
                         data-total="<?= number_format($total, 0, '.', '') ?>">
                        <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                            Coupon Code
                        </label>
                        <form action="<?= base_url('/coupon/apply') ?>" method="post" class="d-flex gap-2 coupon-form" id="couponApplyForm">
                            <?= csrf_field() ?>
                            <input type="text" name="code" id="couponInput" class="filter-price-input flex-grow-1" placeholder="ENTER CODE" autocomplete="off"
                                   value="<?= esc($coupon ?? '') ?>" <?= $coupon ? 'readonly' : '' ?>>
                            <?php if ($coupon): ?>
                                <button type="button" id="couponRemoveBtn" class="btn btn-outline-dark px-3 rounded-0 text-uppercase fw-bold"
                                        style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                                    Remove
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-dark px-3 rounded-0 text-uppercase fw-bold"
                                        style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                                    Apply
                                </button>
                            <?php endif; ?>
                        </form>
                        <div class="text-muted small mt-2 font-serif italic" id="couponHint">
                            <?php if ($coupon): ?>
                                Coupon active. Total reduced by Rp <?= number_format($discount, 0, ',', '.') ?>.
                            <?php else: ?>
                                Try <code>WELCOME10</code> or <code>NEXGEAR50K</code>.
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="font-serif italic" data-summary-subtotal>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 <?= $discount > 0 ? '' : 'd-none' ?>" data-summary-discount-row>
                        <span style="color: #059669;">Discount</span>
                        <span class="font-serif italic" style="color: #059669;" data-summary-discount>− Rp <?= number_format($discount, 0, ',', '.') ?></span>
                    </div>
                    <hr class="border-dark border-opacity-25 my-3">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="h6 mb-0 text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em;">Total</span>
                        <span class="h4 mb-0 font-serif italic" data-summary-total>Rp <?= number_format($total, 0, ',', '.') ?></span>
                    </div>

                    <a href="<?= base_url('/checkout') ?>" class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-4"
                       style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.1em; font-size: 0.85rem;">
                        <span>Proceed to Checkout</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
