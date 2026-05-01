<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-4" data-aos="fade-up">
    <div class="col-lg-8">
        <div class="vp-card p-4 mb-4">
            <h3 class="h6 text-white fw-bold mb-4 text-uppercase tracking-wider">Ordered Items</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="admin-product-item">
                                        <img src="<?= base_url('uploads/products/' . esc($item['product']['image'] ?? 'default-product.svg')) ?>" 
                                             class="admin-product-img" 
                                             onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=100&auto=format&fit=crop'">
                                        <div>
                                            <div class="text-white fw-bold"><?= esc($item['product']['name'] ?? 'Product Deleted') ?></div>
                                            <div class="text-muted small">Rp <?= number_format((float) $item['price'], 0, ',', '.') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($item['quantity']) ?></td>
                                <td class="text-end fw-bold text-white">
                                    Rp <?= number_format((float) ($item['price'] * $item['quantity']), 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="vp-card p-4 mb-4">
            <h3 class="h6 text-white fw-bold mb-4 text-uppercase tracking-wider">Customer Details</h3>
            <div class="mb-4">
                <label class="text-muted small d-block mb-1">Name</label>
                <div class="text-white fw-bold"><?= esc($order['shipping_name']) ?></div>
            </div>
            <div class="mb-4">
                <label class="text-muted small d-block mb-1">Phone</label>
                <div class="text-white fw-bold"><?= esc($order['shipping_phone']) ?></div>
            </div>
            <div class="mb-4">
                <label class="text-muted small d-block mb-1">Address</label>
                <div class="text-white fw-bold">
                    <?= esc($order['shipping_address']) ?><br>
                    <?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_postal_code']) ?>
                </div>
            </div>
        </div>

        <div class="vp-card p-4">
            <h3 class="h6 text-white fw-bold mb-4 text-uppercase tracking-wider">Payment Summary</h3>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Total Paid</span>
                <span class="text-primary h4 fw-bold mb-0">Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></span>
            </div>
            <hr class="border-white border-opacity-10 my-4">
            <button class="btn btn-primary-glow w-100 py-3">
                <i class="bi bi-printer me-2"></i>Print Invoice
            </button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
