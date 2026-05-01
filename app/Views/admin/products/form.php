<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-4" data-aos="fade-up">
    <div class="col-lg-8">
        <div class="vp-card p-4">
            <form action="<?= esc($action) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Product Name</label>
                    <input type="text" name="name" class="form-control vp-input" value="<?= old('name', $product['name'] ?? '') ?>" required placeholder="e.g. Nebula K87 Keyboard">
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Description</label>
                    <textarea name="description" class="form-control vp-input" rows="6" placeholder="Detailed product specifications..."><?= old('description', $product['description'] ?? '') ?></textarea>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Price (IDR)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-white border-opacity-10 text-secondary">Rp</span>
                            <input type="number" name="price" class="form-control vp-input" value="<?= old('price', $product['price'] ?? '') ?>" required placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control vp-input" value="<?= old('stock', $product['stock'] ?? '') ?>" required placeholder="0">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label text-secondary small fw-bold text-uppercase tracking-wider">Product Image</label>
                    <div class="image-upload-wrap p-4 border border-dashed border-white border-opacity-10 rounded-4 text-center">
                        <input type="file" name="image" class="form-control vp-input mb-3" id="productImageInput" accept="image/*">
                        <p class="text-muted small mb-0">Drag and drop or click to upload high-resolution image (PNG, JPG, WEBP)</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary-glow px-5 py-3">
                        <i class="bi bi-save me-2"></i>Save Product Changes
                    </button>
                    <a href="<?= site_url('/admin/products') ?>" class="btn btn-soft px-4 py-3">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="vp-card p-4 mb-4">
            <h3 class="h6 text-white fw-bold mb-3 text-uppercase tracking-wider">Preview Card</h3>
            <div class="product-card">
                <div class="product-media-container">
                    <img src="<?= base_url('uploads/products/' . esc($product['image'] ?? 'default-product.svg')) ?>" 
                         id="previewImage"
                         class="w-100 h-100 object-fit-cover" 
                         alt="Preview"
                         onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=400&auto=format&fit=crop'">
                </div>
                <div class="p-3">
                    <div id="previewName" class="text-white fw-bold mb-1"><?= esc($product['name'] ?? 'Product Name') ?></div>
                    <div id="previewPrice" class="text-primary fw-bold">Rp <?= number_format((float)($product['price'] ?? 0), 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <div class="vp-card p-4">
            <h3 class="h6 text-white fw-bold mb-3 text-uppercase tracking-wider">Quick Guidelines</h3>
            <ul class="text-secondary small ps-3">
                <li class="mb-2">Use high-resolution images (min. 1200x1200px).</li>
                <li class="mb-2">Ensure descriptions include technical specs.</li>
                <li class="mb-2">Double check price for correct currency format.</li>
                <li>Archive products instead of deleting if they have order history.</li>
            </ul>
        </div>
    </div>
</div>

<script>
    // Simple live preview
    document.getElementById('productImageInput').onchange = evt => {
        const [file] = evt.target.files;
        if (file) {
            document.getElementById('previewImage').src = URL.createObjectURL(file);
        }
    }
</script>

<?= $this->endSection() ?>
