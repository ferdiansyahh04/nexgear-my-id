<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Admin Stats -->
<div class="admin-stats-grid" data-aos="fade-up">
    <div class="stat-card">
        <span class="stat-card-label">Total Products</span>
        <div class="stat-card-value"><?= count($products) ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-card-label">Out of Stock</span>
        <div class="stat-card-value text-danger">
            <?= count(array_filter($products, fn($p) => (int)$p['stock'] < 1)) ?>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-card-label">Inventory Value</span>
        <div class="stat-card-value text-primary">
            <?php
                $totalVal = array_reduce($products, fn($carry, $p) => $carry + ((float)$p['price'] * (int)$p['stock']), 0);
                echo 'Rp ' . number_format($totalVal / 1000000, 1) . 'M';
            ?>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="admin-table-wrap" data-aos="fade-up" data-aos-delay="100">
    <div class="p-4 border-bottom border-white border-opacity-5 d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0 text-white fw-bold">Inventory List</h2>
        <a href="<?= site_url('/admin/products/create') ?>" class="btn btn-primary-glow btn-sm">
            <i class="bi bi-plus-lg me-2"></i>New Product
        </a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Product Information</th>
                <th>Price</th>
                <th>Stock Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <div class="admin-product-item">
                            <img src="<?= base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg')) ?>" 
                                 class="admin-product-img" 
                                 alt="<?= esc($product['name']) ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=100&auto=format&fit=crop'">
                            <div>
                                <div class="text-white fw-bold"><?= esc($product['name']) ?></div>
                                <div class="text-muted small">ID: #<?= $product['id'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-white fw-bold">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></div>
                    </td>
                    <td>
                        <?php if ((int)$product['stock'] < 1): ?>
                            <span class="status-pill out-of-stock">Out of Stock</span>
                        <?php elseif ((int)$product['stock'] < 10): ?>
                            <span class="status-pill low-stock"><?= esc($product['stock']) ?> Left</span>
                        <?php else: ?>
                            <span class="status-pill in-stock"><?= esc($product['stock']) ?> in Stock</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= site_url('/admin/products/' . $product['id'] . '/edit') ?>" class="btn btn-soft btn-sm p-2" style="width: 36px; height: 36px;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?= site_url('/admin/products/' . $product['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Archive this product?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-soft btn-sm p-2 text-danger" style="width: 36px; height: 36px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
</script>

<?= $this->endSection() ?>
