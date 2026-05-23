<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-table-wrap p-4 p-lg-5">
            <form action="<?= esc($action) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Asset Name</label>
                    <input type="text" name="name" class="form-control admin-input" value="<?= old('name', $product['name'] ?? '') ?>" required placeholder="e.g. Nebula K87 Keyboard">
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Category</label>
                        <select name="category_id" class="form-control admin-input">
                            <option value="">— uncategorised —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>" <?= (int) old('category_id', $product['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                                    <?= esc($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Detailed Description</label>
                    <textarea name="description" class="form-control admin-input" rows="6" placeholder="Detailed product specifications..."><?= old('description', $product['description'] ?? '') ?></textarea>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Valuation (IDR)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-dark text-dark">Rp</span>
                            <input type="number" name="price" class="form-control admin-input" value="<?= old('price', $product['price'] ?? '') ?>" required placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Vault Stock</label>
                        <input type="number" name="stock" class="form-control admin-input" value="<?= old('stock', $product['stock'] ?? '') ?>" required placeholder="0">
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Primary Visual</label>
                        <div class="p-4 border border-dark border-opacity-10 text-center bg-white">
                            <input type="file" name="image" class="form-control admin-input mb-3" id="productImageInput" accept="image/*">
                            <p class="text-muted mb-0" style="font-size: 0.65rem; font-family: 'Space Grotesk', sans-serif;">MAIN DISPLAY ASSET</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Hover Visual (Secondary)</label>
                        <div class="p-4 border border-dark border-opacity-10 text-center bg-white">
                            <input type="file" name="image_secondary" class="form-control admin-input mb-3" id="productSecondaryImageInput" accept="image/*">
                            <p class="text-muted mb-0" style="font-size: 0.65rem; font-family: 'Space Grotesk', sans-serif;">INTERACTIVE HOVER ASSET</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-dark px-5 py-3 text-uppercase fw-bold rounded-0" style="font-size: 0.8rem; letter-spacing: 0.1em;">
                        Commit Changes
                    </button>
                    <a href="<?= site_url('/admin/products') ?>" class="btn btn-outline-dark px-4 py-3 text-uppercase fw-bold rounded-0" style="font-size: 0.8rem; letter-spacing: 0.1em;">
                        Discard
                    </a>
                </div>
            </form>
        </div>

        <!-- B6 — Gallery upload (only for existing products) -->
        <?php if (! empty($product['id'])): ?>
            <div class="admin-table-wrap p-4 p-lg-5 mt-4">
                <h3 class="font-serif text-muted small text-uppercase mb-4 italic" style="letter-spacing: 0.1em;">
                    Gallery Images <span class="ms-2">(<?= count($extraImages) ?>)</span>
                </h3>

                <form action="<?= site_url('/admin/products/' . (int) $product['id'] . '/images') ?>" method="post" enctype="multipart/form-data" class="d-flex gap-2 align-items-center mb-4 flex-wrap">
                    <?= csrf_field() ?>
                    <input type="file" name="gallery_image" class="form-control admin-input" accept="image/*" required style="max-width: 360px;">
                    <button type="submit" class="btn btn-dark py-2 px-4 rounded-0 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                        + Add Image
                    </button>
                </form>

                <?php if ($extraImages !== []): ?>
                    <div class="row g-3">
                        <?php foreach ($extraImages as $img): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="position-relative border border-dark p-2 bg-white">
                                    <img src="<?= base_url('uploads/products/' . esc($img['path'])) ?>" class="w-100 d-block" style="aspect-ratio: 1/1; object-fit: cover;" alt="">
                                    <form action="<?= site_url('/admin/products/' . (int) $product['id'] . '/images/' . (int) $img['id'] . '/delete') ?>" method="post" class="position-absolute" style="top: 8px; right: 8px;" onsubmit="return confirm('Remove this image from gallery?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-dark btn-sm rounded-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted font-serif italic mb-0">No additional images yet. Add gallery shots above.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap p-4 mt-4">
                <p class="text-muted font-serif italic mb-0">Save the product first to manage its gallery.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="admin-table-wrap p-4 mb-4">
            <h3 class="font-serif text-muted small text-uppercase mb-4 italic" style="letter-spacing: 0.1em;">Public Preview</h3>
            <div class="product-card border border-dark">
                <div class="product-media-container" style="aspect-ratio: 1/1; background: #fff;">
                    <img src="<?= base_url('uploads/products/' . esc($product['image'] ?? 'default-product.svg')) ?>" 
                         id="previewImage"
                         class="w-100 h-100 object-fit-contain p-4" 
                         alt="Preview"
                         onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=400&auto=format&fit=crop'">
                </div>
                <div class="p-3 border-top border-dark">
                    <div id="previewName" class="text-dark fw-bold mb-1 text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;"><?= esc($product['name'] ?? 'Asset Name') ?></div>
                    <div id="previewPrice" class="font-serif text-dark" style="font-size: 1rem; font-style: italic;">Rp <?= number_format((float)($product['price'] ?? 0), 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <div class="admin-table-wrap p-4">
            <h3 class="font-serif text-muted small text-uppercase mb-4 italic" style="letter-spacing: 0.1em;">Guidelines</h3>
            <ul class="text-dark small ps-3 font-serif italic" style="line-height: 1.8;">
                <li class="mb-2">Pick a category to make filters work for shoppers.</li>
                <li class="mb-2">Primary + Hover images appear on the product card.</li>
                <li class="mb-2">Gallery images appear on the product detail page.</li>
                <li>Use PNG/WEBP for crispest visuals.</li>
            </ul>
        </div>
    </div>
</div>


<script nonce="{csp-script-nonce}">
    document.getElementById('productImageInput').onchange = evt => {
        const [file] = evt.target.files;
        if (file) {
            document.getElementById('previewImage').src = URL.createObjectURL(file);
        }
    }
</script>

<?= $this->endSection() ?>
