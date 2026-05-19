<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-4">
    <!-- Categories list -->
    <div class="col-lg-8">
        <div class="admin-table-wrap">
            <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0 text-dark fw-bold text-uppercase" style="letter-spacing: 0.1em;">All Categories</h2>
                <a href="<?= site_url('/admin/products') ?>" class="btn btn-outline-dark btn-sm rounded-0 px-4 py-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                    ← Products
                </a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Order</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><span class="font-serif italic"><?= (int) $cat['sort_order'] ?></span></td>
                                <td>
                                    <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;"><?= esc($cat['name']) ?></div>
                                    <?php if (! empty($cat['description'])): ?>
                                        <div class="text-muted font-serif italic" style="font-size: 0.75rem;"><?= esc($cat['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><code class="font-monospace small"><?= esc($cat['slug']) ?></code></td>
                                <td>
                                    <a href="<?= site_url('/admin/products?category=' . (int) $cat['id']) ?>"
                                       class="status-pill" style="background:#f5f5f5;border:1px solid #000;color:#000;">
                                        <?= (int) $cat['product_count'] ?> linked
                                    </a>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-outline-dark btn-sm rounded-0"
                                                style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#category-edit-<?= (int) $cat['id'] ?>"
                                                aria-label="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="<?= site_url('/admin/categories/' . (int) $cat['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Remove this category? Products will become uncategorised.')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse" id="category-edit-<?= (int) $cat['id'] ?>">
                                <td colspan="5" class="bg-light">
                                    <form action="<?= site_url('/admin/categories/' . (int) $cat['id']) ?>" method="post" class="row g-3 p-3">
                                        <?= csrf_field() ?>
                                        <div class="col-md-4">
                                            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Name</label>
                                            <input type="text" name="name" value="<?= esc($cat['name']) ?>" class="form-control admin-input" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Slug</label>
                                            <input type="text" name="slug" value="<?= esc($cat['slug']) ?>" class="form-control admin-input">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Order</label>
                                            <input type="number" name="sort_order" value="<?= (int) $cat['sort_order'] ?>" class="form-control admin-input">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-dark w-100 py-2 rounded-0 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                                                Save Changes
                                            </button>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Description</label>
                                            <input type="text" name="description" value="<?= esc($cat['description']) ?>" class="form-control admin-input">
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($categories === []): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted font-serif italic">No categories yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- New category form -->
    <div class="col-lg-4">
        <div class="admin-table-wrap p-4">
            <h3 class="font-serif text-muted small text-uppercase mb-4 italic" style="letter-spacing: 0.1em;">New Category</h3>
            <form action="<?= site_url('/admin/categories') ?>" method="post" class="vstack gap-3">
                <?= csrf_field() ?>
                <div>
                    <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Name</label>
                    <input type="text" name="name" class="form-control admin-input" required placeholder="e.g. Streaming Gear">
                </div>
                <div>
                    <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Slug <span class="text-muted">(optional)</span></label>
                    <input type="text" name="slug" class="form-control admin-input" placeholder="auto-generated from name">
                </div>
                <div>
                    <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Description</label>
                    <textarea name="description" class="form-control admin-input" rows="3"></textarea>
                </div>
                <div>
                    <label class="font-serif text-muted small text-uppercase mb-2 d-block italic">Sort Order</label>
                    <input type="number" name="sort_order" value="0" class="form-control admin-input">
                </div>
                <button type="submit" class="btn btn-dark py-3 rounded-0 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.1em;">
                    Create Category
                </button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
