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

<!-- Top stats grid (B13) -->
<div class="admin-stats-grid">
    <div class="stat-card" data-aos="fade-up">
        <span class="stat-card-label">Total Revenue</span>
        <div class="stat-card-value">
            Rp <span data-counter="<?= number_format($totalRevenue / 1000000, 2, '.', '') ?>" data-counter-decimals="2">0</span>M
        </div>
        <div class="text-muted small mt-2 font-serif italic" style="font-size: 0.78rem;">
            <?= number_format($totalOrders) ?> orders · all time
        </div>
    </div>

    <div class="stat-card" data-aos="fade-up" data-aos-delay="60">
        <span class="stat-card-label">Last 30 Days</span>
        <div class="stat-card-value">
            Rp <span data-counter="<?= number_format($monthRevenue / 1000000, 2, '.', '') ?>" data-counter-decimals="2">0</span>M
        </div>
        <div class="text-muted small mt-2 font-serif italic" style="font-size: 0.78rem;">
            <?= number_format($monthOrders) ?> orders this month
        </div>
    </div>

    <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
        <span class="stat-card-label">Inventory Value</span>
        <div class="stat-card-value">
            Rp <span data-counter="<?= number_format($inventoryValue / 1000000, 1, '.', '') ?>" data-counter-decimals="1">0</span>M
        </div>
        <div class="text-muted small mt-2 font-serif italic" style="font-size: 0.78rem;">
            <?= count($lowStock) ?> low · <?= $outOfStockCount ?> sold out
        </div>
    </div>

    <div class="stat-card" data-aos="fade-up" data-aos-delay="180">
        <span class="stat-card-label">Customers</span>
        <div class="stat-card-value" data-counter="<?= $customerCount ?>">0</div>
        <div class="text-muted small mt-2 font-serif italic" style="font-size: 0.78rem;">
            <?= $newsletterCount ?> newsletter · <?= $newMessages ?> new msgs
        </div>
    </div>
</div>

<!-- Pending order pipeline -->
<div class="admin-table-wrap p-4 mb-4">
    <h3 class="font-serif text-muted small text-uppercase mb-3 italic" style="letter-spacing: 0.1em;">
        Order Pipeline
    </h3>
    <div class="d-flex gap-2 flex-wrap">
        <?php
        $pipelineStages = ['checked_out', 'paid', 'processing', 'shipped'];
        foreach ($pipelineStages as $key):
            $count = (int) ($pendingByStatus[$key] ?? 0);
            $info  = $statusMap[$key] ?? ['label' => ucfirst($key), 'tone' => 'muted'];
        ?>
            <a href="<?= site_url('/admin/orders?status=' . urlencode($key)) ?>" class="status-pill status-tone-<?= esc($info['tone']) ?>" style="font-size: 0.85rem; padding: 8px 16px; gap: 10px;">
                <?= esc($info['label']) ?>
                <strong><?= $count ?></strong>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Chart row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="admin-table-wrap p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="font-serif text-muted small text-uppercase mb-0 italic" style="letter-spacing: 0.1em;">
                    Daily Revenue · Last 30 Days
                </h3>
                <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                    Rp & orders
                </span>
            </div>
            <div class="chart-shell">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-table-wrap p-4 h-100">
            <h3 class="font-serif text-muted small text-uppercase mb-3 italic" style="letter-spacing: 0.1em;">
                Quick Actions
            </h3>
            <div class="d-grid gap-2">
                <a href="<?= site_url('/admin/products/create') ?>" class="btn btn-dark py-2 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-3"
                   style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                    <span>+ New Product</span><span>→</span>
                </a>
                <a href="<?= site_url('/admin/orders?status=paid') ?>" class="btn btn-outline-dark py-2 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-3"
                   style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                    <span>Process Paid Orders</span><span>→</span>
                </a>
                <a href="<?= site_url('/admin/messages?status=new') ?>" class="btn btn-outline-dark py-2 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-3"
                   style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                    <span>New Messages (<?= $newMessages ?>)</span><span>→</span>
                </a>
                <a href="<?= site_url('/admin/categories') ?>" class="btn btn-outline-dark py-2 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-3"
                   style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                    <span>Manage Categories</span><span>→</span>
                </a>
            </div>

            <?php if ($lowStock !== []): ?>
                <h4 class="font-serif text-muted small text-uppercase mt-4 mb-2 italic" style="letter-spacing: 0.1em;">
                    Low-Stock Watchlist
                </h4>
                <ul class="list-unstyled mb-0">
                    <?php foreach (array_slice($lowStock, 0, 5) as $item): ?>
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom border-dark border-opacity-10">
                            <a href="<?= site_url('/admin/products/' . (int) $item['id'] . '/edit') ?>" class="text-dark text-decoration-none small text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.04em;">
                                <?= esc($item['name']) ?>
                            </a>
                            <span class="status-pill status-tone-warning"><?= (int) $item['stock'] ?> left</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Top products + recent orders -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="admin-table-wrap">
            <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center">
                <h3 class="font-serif text-muted small text-uppercase mb-0 italic" style="letter-spacing: 0.1em;">Top Products</h3>
                <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">By units sold</span>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Units</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($topProducts === []): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted font-serif italic">No sales yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $p): ?>
                                <tr>
                                    <td>
                                        <div class="admin-product-item">
                                            <img src="<?= base_url('uploads/products/' . esc($p['image'] ?? 'default-product.svg')) ?>"
                                                 class="admin-product-img"
                                                 onerror="this.src='https://images.unsplash.com/photo-1603481546238-487240415921?q=80&w=100&auto=format&fit=crop'">
                                            <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;">
                                                <?= esc($p['name'] ?? 'Removed product') ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold"><?= (int) $p['units'] ?></td>
                                    <td class="text-end font-serif italic">
                                        Rp <?= _rp_compact((float) $p['revenue']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="admin-table-wrap">
            <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center">
                <h3 class="font-serif text-muted small text-uppercase mb-0 italic" style="letter-spacing: 0.1em;">Recent Orders</h3>
                <a href="<?= site_url('/admin/orders') ?>" class="text-dark text-decoration-none text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
                    View All →
                </a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentOrders === []): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted font-serif italic">No orders yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order):
                                $info = $statusMap[$order['status']] ?? ['label' => ucfirst($order['status']), 'tone' => 'muted'];
                            ?>
                                <tr style="cursor: pointer;" onclick="window.location='<?= site_url('/admin/orders/' . (int) $order['id']) ?>'">
                                    <td><span class="font-serif italic">#<?= (int) $order['id'] ?></span></td>
                                    <td>
                                        <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem;"><?= esc($order['shipping_name'] ?? '—') ?></div>
                                        <div class="text-muted small font-serif italic"><?= date('d M, H:i', strtotime($order['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <span class="status-pill status-tone-<?= esc($info['tone']) ?>"><?= esc($info['label']) ?></span>
                                    </td>
                                    <td class="text-end font-serif italic">Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js — load only on dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('revenueChart');
    if (!ctx || typeof Chart === 'undefined') return;

    const labels  = <?= json_encode($chartLabels) ?>;
    const revenue = <?= json_encode($chartRevenue) ?>;
    const orders  = <?= json_encode($chartOrders) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Revenue (Rp)',
                    data: revenue,
                    borderColor: '#000',
                    backgroundColor: 'rgba(0,0,0,0.06)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#000',
                    fill: true,
                    tension: 0.25,
                    yAxisID: 'y',
                },
                {
                    type: 'bar',
                    label: 'Orders',
                    data: orders,
                    backgroundColor: 'rgba(212, 255, 55, 0.6)',
                    borderColor: '#000',
                    borderWidth: 1,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'bottom', labels: { font: { family: 'Space Grotesk' } } },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.dataset.label.startsWith('Revenue')) {
                                return 'Revenue: Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID');
                            }
                            return 'Orders: ' + ctx.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    ticks: { callback: v => 'Rp ' + Number(v).toLocaleString('id-ID') }
                },
                y2: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false },
                    ticks: { maxRotation: 0, autoSkipPadding: 12 }
                }
            }
        }
    });
})();
</script>
<?= $this->endSection() ?>
