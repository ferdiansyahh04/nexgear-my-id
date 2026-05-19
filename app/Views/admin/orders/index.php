<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Filter pills -->
<div class="mb-4 d-flex gap-2 flex-wrap">
    <a href="<?= base_url('/admin/orders') ?>" class="filter-chip <?= $filterStatus === '' ? 'is-active' : '' ?>">All</a>
    <?php foreach ($statusMap as $key => $info):
        if ($key === 'active') continue;
    ?>
        <a href="<?= base_url('/admin/orders?status=' . urlencode($key)) ?>"
           class="filter-chip <?= $filterStatus === $key ? 'is-active' : '' ?>">
            <?= esc($info['label']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Orders Table -->
<div class="admin-table-wrap">
    <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center bg-white">
        <h2 class="h6 mb-0 text-dark fw-bold text-uppercase" style="letter-spacing: 0.1em;">
            Order Manifest <?= $filterStatus !== '' ? '— ' . esc($statusMap[$filterStatus]['label']) : '' ?>
        </h2>
        <div class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            <?= count($orders) ?> Record<?= count($orders) === 1 ? '' : 's' ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Entry ID</th>
                    <th>Customer Details</th>
                    <th>Total Value</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order):
                    $status = $statusMap[$order['status']] ?? ['label' => ucfirst($order['status']), 'tone' => 'muted'];
                ?>
                    <tr>
                        <td>
                            <span class="text-dark fw-bold" style="font-family: 'Space Grotesk', sans-serif;">#<?= $order['id'] ?></span>
                        </td>
                        <td>
                            <div class="text-dark fw-bold text-uppercase" style="font-size: 0.85rem;"><?= esc($order['shipping_name'] ?: 'Guest Curator') ?></div>
                            <div class="text-muted font-serif italic" style="font-size: 0.75rem;"><?= esc($order['shipping_phone']) ?></div>
                        </td>
                        <td>
                            <div class="text-dark font-serif" style="font-size: 1rem;">Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></div>
                        </td>
                        <td>
                            <span class="status-pill status-tone-<?= esc($status['tone']) ?>">
                                <?= esc($status['label']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-dark fw-bold" style="font-size: 0.8rem;"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                            <div class="text-muted font-serif italic" style="font-size: 0.7rem;"><?= date('H:i', strtotime($order['created_at'])) ?></div>
                        </td>
                        <td class="text-end">
                            <a href="<?= site_url('/admin/orders/' . $order['id']) ?>" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.1em;">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted font-serif italic">The vault is currently empty of orders.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?= $this->endSection() ?>
