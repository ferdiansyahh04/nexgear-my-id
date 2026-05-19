<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$status = $statusMap[$order['status']] ?? ['label' => ucfirst($order['status']), 'tone' => 'muted', 'description' => ''];
?>
<div class="row g-4">
    <div class="col-lg-8">
        <!-- Status panel -->
        <div class="admin-table-wrap p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <div class="text-muted text-uppercase fw-bold mb-1" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                        Current Status
                    </div>
                    <span class="status-pill status-tone-<?= esc($status['tone']) ?>" style="font-size: 0.85rem;">
                        <?= esc($status['label']) ?>
                    </span>
                </div>

                <?php if ($allowedTransitions !== []): ?>
                    <form action="<?= site_url('/admin/orders/' . (int) $order['id'] . '/status') ?>" method="post" class="d-flex gap-2 align-items-center flex-wrap">
                        <?= csrf_field() ?>
                        <label class="text-muted text-uppercase fw-bold mb-0 me-2" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                            Move to
                        </label>
                        <select name="status" class="admin-input" style="min-width: 180px;" required>
                            <option value="">— select status —</option>
                            <?php foreach ($allowedTransitions as $next): ?>
                                <option value="<?= esc($next) ?>"><?= esc($statusMap[$next]['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-dark px-4 py-2 rounded-0 text-uppercase fw-bold"
                                style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;"
                                onclick="return confirm('Update order status?')">
                            Apply →
                        </button>
                    </form>
                <?php else: ?>
                    <span class="text-muted font-serif italic" style="font-size: 0.85rem;">No transitions available.</span>
                <?php endif; ?>
            </div>

            <!-- Timeline -->
            <div class="order-timeline order-timeline--admin">
                <?php foreach ($timeline as $i => $stage): ?>
                    <div class="order-timeline-step is-<?= esc($stage['state']) ?>">
                        <div class="order-timeline-dot"><?= $i + 1 ?></div>
                        <div class="order-timeline-label"><?= esc($stage['label']) ?></div>
                    </div>
                    <?php if ($i < count($timeline) - 1): ?>
                        <div class="order-timeline-line is-<?= esc($stage['state']) ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <p class="text-muted font-serif italic mt-3 mb-0" style="font-size: 0.85rem;">
                <?= esc($status['description']) ?>
            </p>
        </div>

        <!-- Items -->
        <div class="admin-table-wrap p-4 mb-4">
            <h3 class="font-serif text-muted small text-uppercase mb-4 italic" style="letter-spacing: 0.1em;">Order Contents</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Asset Details</th>
                            <th>Qty</th>
                            <th class="text-end">Value</th>
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
                                            <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;"><?= esc($item['product']['name'] ?? 'Asset Expired') ?></div>
                                            <div class="font-serif text-muted italic" style="font-size: 0.85rem;">Unit: Rp <?= number_format((float) $item['price'], 0, ',', '.') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-dark fw-bold"><?= esc($item['quantity']) ?></td>
                                <td class="text-end text-dark font-serif" style="font-size: 1.1rem; font-style: italic;">
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
        <div class="admin-table-wrap p-4 mb-4">
            <h3 class="font-serif text-muted small text-uppercase mb-4 italic" style="letter-spacing: 0.1em;">Curator Details</h3>
            <div class="mb-4">
                <label class="font-serif text-muted small d-block mb-1 italic">Identity</label>
                <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif;"><?= esc($order['shipping_name']) ?></div>
            </div>
            <div class="mb-4">
                <label class="font-serif text-muted small d-block mb-1 italic">Contact</label>
                <div class="text-dark fw-bold"><?= esc($order['shipping_phone']) ?></div>
            </div>
            <div class="mb-4">
                <label class="font-serif text-muted small d-block mb-1 italic">Destination</label>
                <div class="text-dark" style="line-height: 1.6; font-size: 0.9rem;">
                    <?= esc($order['shipping_address']) ?><br>
                    <?= esc($order['shipping_city']) ?>, <?= esc($order['shipping_postal_code']) ?>
                </div>
            </div>
        </div>

        <div class="admin-table-wrap p-4">
            <h3 class="font-serif text-muted small text-uppercase mb-4 italic" style="letter-spacing: 0.1em;">Transaction Summary</h3>
            <div class="d-flex justify-content-between align-items-end mb-2">
                <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.1em;">Final Valuation</span>
                <span class="font-serif text-dark h3 mb-0" style="font-style: italic;">Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></span>
            </div>
            <hr class="border-dark border-opacity-10 my-4">
            <a href="<?= site_url('/admin/orders/' . (int) $order['id'] . '/invoice/pdf') ?>" target="_blank"
               class="btn btn-dark w-100 py-2 text-uppercase fw-bold rounded-0 mb-2"
               style="font-size: 0.7rem; letter-spacing: 0.1em;">
                <i class="bi bi-file-earmark-pdf me-2"></i>Download Invoice PDF
            </a>
            <a href="<?= base_url('/admin/orders') ?>" class="btn btn-outline-dark w-100 py-2 text-uppercase fw-bold rounded-0"
               style="font-size: 0.7rem; letter-spacing: 0.1em;">
                ← Back to Orders
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
