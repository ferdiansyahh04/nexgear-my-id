<?php
/**
 * Reusable products grid partial.
 * Used by both the full /products page and the AJAX filter response.
 */
?>
<?php if ($products === []): ?>
    <div class="col-12 py-5 text-center">
        <p class="font-serif italic text-muted mb-3">No objects match those filters.</p>
        <button type="button" class="or-split-link justify-content-center bg-transparent border-0 p-0"
                data-filter-clear>
            Clear Filters <span>→</span>
        </button>
    </div>
<?php else: ?>
    <?php foreach ($products as $product): ?>
        <div class="col-md-6 col-lg-4 border-end border-bottom border-dark">
            <?= view('products/_card', ['product' => $product]) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
