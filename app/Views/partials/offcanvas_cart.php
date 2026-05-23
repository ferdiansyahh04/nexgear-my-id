<?php
/**
 * Offcanvas Cart Partial
 *
 * Receives data via controller/service, not querying models directly.
 * Variables available:
 *   $cartData (optional) — pre-fetched items from CartService::items()
 */
$cart      = session('cart') ?? [];
$items     = $cartData ?? [];
$subtotal  = 0;
foreach ($items as $item) {
    $subtotal += $item['subtotal'];
}
$discount = $items === [] ? 0 : (new \App\Libraries\CouponService())->currentDiscount($subtotal);
$total    = max(0, $subtotal - $discount);
$couponCode = (new \App\Libraries\CouponService())->applied();
?>
<div class="offcanvas offcanvas-end offcanvas-cart" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasCartLabel">Your Selection <span class="font-serif">(<?= array_sum($cart) ?>)</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (empty($items)): ?>
            <div class="text-center py-5">
                <p class="text-muted">Your cart is currently empty.</p>
                <a href="<?= base_url('/collection') ?>" class="or-split-link mt-3">Start Exploring <span>→</span></a>
            </div>
        <?php else: ?>
            <div id="cartItemsContainer">
                <?php foreach ($items as $item):
                    $product  = $item['product'];
                    $qty      = (int) $item['qty'];
                    $stock    = (int) $product['stock'];
                    $imageFile = trim((string) ($product['image'] ?? ''));
                    if ($imageFile === '' || ! is_file(FCPATH . 'uploads/products/' . $imageFile)) {
                        $imageFile = 'default-product.svg';
                    }
                    $image = base_url('uploads/products/' . rawurlencode($imageFile));
                ?>
                    <div class="cart-item border-bottom border-dark border-opacity-10 py-3 d-flex align-items-center"
                         data-product-id="<?= (int) $product['id'] ?>"
                         data-stock="<?= $stock ?>">
                        <div class="cart-item-img me-3" style="width: 70px; height: 70px; flex-shrink: 0; background: #f8f8f8;">
                            <img src="<?= $image ?>" alt="<?= esc($product['name']) ?>" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="cart-item-info flex-grow-1">
                            <h6 class="cart-item-title mb-1 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;"><?= esc($product['name']) ?></h6>
                            <span class="cart-item-price font-serif d-block mb-2" style="font-size: 0.9rem;">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></span>
                            <div class="cart-qty-stepper" role="group" aria-label="Adjust quantity for <?= esc($product['name']) ?>">
                                <button type="button" class="cart-qty-btn cart-qty-decrease"
                                        data-id="<?= (int) $product['id'] ?>" data-delta="-1"
                                        aria-label="Decrease quantity"
                                        <?= $qty <= 1 ? '' : '' ?>>
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="cart-qty-value font-serif" data-qty><?= esc($qty) ?></span>
                                <button type="button" class="cart-qty-btn cart-qty-increase"
                                        data-id="<?= (int) $product['id'] ?>" data-delta="1"
                                        aria-label="Increase quantity"
                                        <?= $qty >= $stock ? 'disabled' : '' ?>>
                                    <i class="bi bi-plus"></i>
                                </button>
                                <span class="cart-qty-subtotal font-serif italic ms-3" data-subtotal>
                                    Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                        <div class="cart-item-actions">
                             <button class="btn btn-link text-dark p-0 remove-item" data-id="<?= esc($product['id']) ?>" aria-label="Remove item"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($items)): ?>
        <div class="offcanvas-footer">
            <?php if ($discount > 0): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span class="text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">Subtotal</span>
                    <span class="font-serif italic">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 small">
                    <span class="text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em; color: #059669;">
                        Discount<?= $couponCode ? ' (' . esc($couponCode) . ')' : '' ?>
                    </span>
                    <span class="font-serif italic" style="color: #059669;">− Rp <?= number_format($discount, 0, ',', '.') ?></span>
                </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 0.1em;">Total</span>
                <span class="cart-item-price fs-4 font-serif" style="font-style: italic;">Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
            <div class="d-grid gap-2">
                <a href="<?= base_url('/checkout') ?>" class="btn btn-dark text-uppercase py-3 fw-bold" style="letter-spacing: 0.1em; font-size: 0.8rem;">Proceed to Checkout</a>
                <a href="<?= base_url('/cart') ?>" class="btn btn-link text-dark text-decoration-none text-uppercase fw-bold" style="letter-spacing: 0.1em; font-size: 0.7rem;">View Detailed Bag</a>
            </div>
        </div>
    <?php endif; ?>
</div>
