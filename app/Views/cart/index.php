<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container pb-5">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Session cart</p>
            <h1>Your Cart</h1>
        </div>
        <a class="btn btn-soft" href="<?= site_url('/products') ?>"><i class="bi bi-arrow-left me-2"></i>Products</a>
    </div>

    <?php if ($items === []): ?>
        <div class="empty-state">Cart is empty.</div>
    <?php else: ?>
        <form action="<?= base_url('/cart/update') ?>" method="post">
            <?= csrf_field() ?>
            <div class="table-shell">
                <table class="table align-middle vp-table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="d-none d-md-table-cell">Price</th>
                            <th class="qty-col">Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): 
                            $product = $item['product'];
                            $productImage = base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg'));
                            if ($product['image'] === 'default-product.svg' || empty($product['image'])) {
                                $name = strtolower($product['name']);
                                if (strpos($name, 'keyboard') !== false) $productImage = 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=200&auto=format&fit=crop';
                                elseif (strpos($name, 'mouse') !== false) $productImage = 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?q=80&w=200&auto=format&fit=crop';
                                elseif (strpos($name, 'headset') !== false) $productImage = 'https://images.unsplash.com/photo-1583394838336-acd977730f8a?q=80&w=200&auto=format&fit=crop';
                                else $productImage = 'https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=200&auto=format&fit=crop';
                            }
                        ?>
                            <tr>
                                <td>
                                    <div class="cart-product d-flex align-items-center gap-3">
                                        <div class="cart-img-wrap">
                                            <img src="<?= $productImage ?>" alt="<?= esc($product['name']) ?>" class="rounded-3">
                                        </div>
                                        <div class="cart-info">
                                            <span class="d-block fw-bold text-white"><?= esc($product['name']) ?></span>
                                            <span class="d-md-none text-muted small">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell text-secondary">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></td>
                                <td>
                                    <input class="form-control vp-input qty-input" type="number" min="1" max="<?= esc($product['stock']) ?>" name="qty[<?= esc($product['id']) ?>]" value="<?= esc($item['qty']) ?>">
                                </td>
                                <td class="fw-bold text-white">Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?></td>
                                <td class="text-end">
                                    <button class="btn btn-ghost text-danger p-2" formaction="<?= base_url('/cart/remove/' . $product['id']) ?>" formmethod="post">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="cart-actions mt-4">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="d-flex gap-2">
                            <button class="btn btn-soft btn-sm" type="submit"><i class="bi bi-arrow-repeat me-2"></i>Update</button>
                            <button class="btn btn-ghost btn-sm" formaction="<?= base_url('/cart/clear') ?>" formmethod="post"><i class="bi bi-x-circle me-2"></i>Clear</button>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="total-summary-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary">Grand Total</span>
                                <span class="h4 mb-0 fw-bold text-white">Rp <?= number_format((float) $total, 0, ',', '.') ?></span>
                            </div>
                            <a class="btn btn-primary-glow w-100 py-3" href="<?= base_url('/checkout') ?>">
                                Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
