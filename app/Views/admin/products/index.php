<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Admin Stats -->
<div class="admin-stats-grid">
    <div class="stat-card" data-aos="fade-up">
        <span class="stat-card-label">Total Inventory</span>
        <div class="stat-card-value" data-counter="<?= count($products) ?>">0</div>
    </div>
    <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
        <span class="stat-card-label">Restock Required</span>
        <div class="stat-card-value" style="color: #ff3b30 !important;"
             data-counter="<?= count(array_filter($products, fn($p) => (int)$p['stock'] < 1)) ?>">0</div>
    </div>
    <div class="stat-card" data-aos="fade-up" data-aos-delay="160">
        <span class="stat-card-label">Est. Asset Value</span>
        <div class="stat-card-value">
            <?php
                $totalVal = array_reduce($products, fn($carry, $p) => $carry + ((float)$p['price'] * (int)$p['stock']), 0);
                $millions = $totalVal / 1000000;
            ?>
            Rp <span data-counter="<?= number_format($millions, 1, '.', '') ?>" data-counter-decimals="1">0.0</span>M
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="admin-table-wrap">
    <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0 text-dark fw-bold text-uppercase" style="letter-spacing: 0.1em;">Archive Inventory</h2>
        <div class="d-flex gap-2">
            <a href="<?= site_url('/admin/categories') ?>" class="btn btn-outline-dark btn-sm rounded-0 px-4 py-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                <i class="bi bi-tags me-2"></i>Categories
            </a>
            <a href="<?= site_url('/admin/products/create') ?>" class="btn btn-dark btn-sm rounded-0 px-4 py-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                <i class="bi bi-plus-lg me-2"></i>New Entry
            </a>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th>Pricing</th>
                    <th>Availability</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Resolve category labels in one query
                $catLabels = [];
                if ($products !== []) {
                    $catRows = (new \App\Models\CategoryModel())->findAll();
                    foreach ($catRows as $c) $catLabels[(int) $c['id']] = $c['name'];
                }
                ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="admin-product-item">
                                <img src="<?= base_url('uploads/products/' . esc($product['image'] ?: 'default-product.svg')) ?>" 
                                     class="admin-product-img" 
                                     alt="<?= esc($product['name']) ?>"
                                     style="border: 1px solid #eee;"
                                     onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=100&auto=format&fit=crop'">
                                <div>
                                    <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;"><?= esc($product['name']) ?></div>
                                    <div class="text-muted font-serif italic" style="font-size: 0.75rem;">UID: #<?= $product['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php $catId = (int) ($product['category_id'] ?? 0); ?>
                            <?php if (isset($catLabels[$catId])): ?>
                                <span class="status-pill" style="background:#f5f5f5;border:1px solid #000;color:#000;">
                                    <?= esc($catLabels[$catId]) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted font-serif italic" style="font-size:0.85rem;">— uncategorised</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="text-dark font-serif" style="font-size: 1rem;">Rp <?= number_format((float) $product['price'], 0, ',', '.') ?></div>
                        </td>
                        <td>
                            <?php if ((int)$product['stock'] < 1): ?>
                                <span class="status-pill out-of-stock">Depleted</span>
                            <?php elseif ((int)$product['stock'] < 10): ?>
                                <span class="status-pill low-stock"><?= esc($product['stock']) ?> Units Left</span>
                            <?php else: ?>
                                <span class="status-pill in-stock"><?= esc($product['stock']) ?> In Vault</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="<?= site_url('/admin/products/' . $product['id'] . '/edit') ?>" class="btn btn-outline-dark btn-sm rounded-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="<?= site_url('/admin/products/' . $product['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Archive this record permanently?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<?= $this->endSection() ?>
