<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
function _rp_compact(float $n): string {
    if ($n >= 1_000_000_000) return number_format($n / 1_000_000_000, 1) . 'B';
    if ($n >= 1_000_000)     return number_format($n / 1_000_000, 1) . 'M';
    if ($n >= 1_000)         return number_format($n / 1_000, 0) . 'K';
    return number_format($n, 0);
}
?>

<!-- Filter bar -->
<div class="admin-table-wrap p-4 mb-4">
    <form method="get" action="<?= site_url('/admin/reports') ?>" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Start Date</label>
            <input type="date" name="start" value="<?= esc($start) ?>" class="form-control admin-input">
        </div>
        <div class="col-md-3">
            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">End Date</label>
            <input type="date" name="end" value="<?= esc($end) ?>" class="form-control admin-input">
        </div>
        <div class="col-md-3">
            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Quick Range</label>
            <div class="d-flex gap-1 flex-wrap">
                <?php
                $today  = date('Y-m-d');
                $ranges = [
                    'Today'    => [$today, $today],
                    '7 Days'   => [date('Y-m-d', strtotime('-6 days')), $today],
                    '30 Days'  => [date('Y-m-d', strtotime('-29 days')), $today],
                    'This Mo.' => [date('Y-m-01'), $today],
                ];
                foreach ($ranges as $label => [$rs, $re]):
                    $href = site_url('/admin/reports') . '?start=' . $rs . '&end=' . $re;
                ?>
                    <a href="<?= $href ?>" class="filter-chip <?= ($start === $rs && $end === $re) ? 'is-active' : '' ?>">
                        <?= esc($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark py-2 px-4 rounded-0 text-uppercase fw-bold flex-grow-1"
                    style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                Apply
            </button>
            <a href="<?= site_url('/admin/reports/export/csv') . '?start=' . $start . '&end=' . $end ?>"
               class="btn btn-outline-dark py-2 px-4 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                <i class="bi bi-download me-1"></i> CSV
            </a>
        </div>
    </form>
</div>

<!-- Top stats -->
<div class="admin-stats-grid">
    <div class="stat-card">
        <span class="stat-card-label">Revenue</span>
        <div class="stat-card-value">
            Rp <span data-counter="<?= number_format($totals['revenue'] / 1000000, 2, '.', '') ?>" data-counter-decimals="2">0</span>M
        </div>
        <div class="text-muted small mt-2 font-serif italic" style="font-size: 0.78rem;">
            <?= esc($start) ?> → <?= esc($end) ?>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-card-label">Orders</span>
        <div class="stat-card-value" data-counter="<?= (int) $totals['orders'] ?>">0</div>
    </div>
    <div class="stat-card">
        <span class="stat-card-label">Avg. Order Value</span>
        <div class="stat-card-value">
            Rp <span data-counter="<?= number_format($totals['aov'] / 1000, 0, '.', '') ?>">0</span>K
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-card-label">Discounts Given</span>
        <div class="stat-card-value">
            Rp <span data-counter="<?= number_format($totals['discounts'] / 1000, 0, '.', '') ?>">0</span>K
        </div>
    </div>
</div>

<!-- Status breakdown + top products -->
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="admin-table-wrap">
            <div class="p-4 border-bottom border-dark">
                <h3 class="font-serif text-muted small text-uppercase m-0 italic" style="letter-spacing: 0.1em;">Status Breakdown</h3>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead><tr><th>Status</th><th class="text-end">Orders</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                        <?php if ($byStatus === []): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted font-serif italic">No data in range.</td></tr>
                        <?php else: ?>
                            <?php foreach ($byStatus as $row):
                                $info = $statusMap[$row['status']] ?? ['label' => ucfirst($row['status']), 'tone' => 'muted'];
                            ?>
                                <tr>
                                    <td><span class="status-pill status-tone-<?= esc($info['tone']) ?>"><?= esc($info['label']) ?></span></td>
                                    <td class="text-end fw-bold"><?= (int) $row['c'] ?></td>
                                    <td class="text-end font-serif italic">Rp <?= _rp_compact((float) $row['s']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="admin-table-wrap">
            <div class="p-4 border-bottom border-dark">
                <h3 class="font-serif text-muted small text-uppercase m-0 italic" style="letter-spacing: 0.1em;">Top Products in Range</h3>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead><tr><th>Product</th><th class="text-end">Units</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                        <?php if ($topProducts === []): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted font-serif italic">No sales.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $p): ?>
                                <tr>
                                    <td>
                                        <?php if (! empty($p['id'])): ?>
                                            <a href="<?= site_url('/admin/products/' . (int) $p['id'] . '/edit') ?>" class="text-dark text-decoration-none text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;">
                                                <?= esc($p['name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted font-serif italic">— removed product —</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold"><?= (int) $p['units'] ?></td>
                                    <td class="text-end font-serif italic">Rp <?= _rp_compact((float) $p['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Orders in range -->
<div class="admin-table-wrap">
    <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center">
        <h3 class="font-serif text-muted small text-uppercase m-0 italic" style="letter-spacing: 0.1em;">Orders in Range</h3>
        <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            Showing <?= count($orders) ?>
            <?= count($orders) >= 100 ? '(capped at 100, export CSV for full list)' : '' ?>
        </span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th><th>Date</th><th>Customer</th><th>Status</th><th class="text-end">Total</th><th class="text-end">Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders === []): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted font-serif italic">No orders in this range.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order):
                        $info = $statusMap[$order['status']] ?? ['label' => ucfirst($order['status']), 'tone' => 'muted'];
                    ?>
                        <tr>
                            <td><span class="font-serif italic">#<?= (int) $order['id'] ?></span></td>
                            <td>
                                <div class="text-dark fw-bold" style="font-size: 0.8rem;"><?= date('d M Y', strtotime($order['created_at'])) ?></div>
                                <div class="text-muted font-serif italic" style="font-size: 0.7rem;"><?= date('H:i', strtotime($order['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem;"><?= esc($order['shipping_name'] ?: '—') ?></div>
                                <div class="text-muted small font-serif italic"><?= esc($order['shipping_city'] ?? '') ?></div>
                            </td>
                            <td><span class="status-pill status-tone-<?= esc($info['tone']) ?>"><?= esc($info['label']) ?></span></td>
                            <td class="text-end font-serif italic">Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></td>
                            <td class="text-end">
                                <a href="<?= site_url('/admin/orders/' . (int) $order['id'] . '/invoice/pdf') ?>" target="_blank"
                                   class="btn btn-outline-dark btn-sm rounded-0 px-3 text-uppercase fw-bold"
                                   style="font-size: 0.65rem; letter-spacing: 0.1em;">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
