<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Orders Table -->
<div class="admin-table-wrap" data-aos="fade-up">
    <div class="p-4 border-bottom border-white border-opacity-5 d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0 text-white fw-bold">Recent Orders</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-soft btn-sm px-3">Export CSV</button>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Date</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <span class="text-primary fw-bold">#<?= $order['id'] ?></span>
                    </td>
                    <td>
                        <div class="text-white fw-bold"><?= esc($order['shipping_name'] ?: 'Guest Customer') ?></div>
                        <div class="text-muted small"><?= esc($order['shipping_phone']) ?></div>
                    </td>
                    <td>
                        <div class="text-white fw-bold">Rp <?= number_format((float) $order['total'], 0, ',', '.') ?></div>
                    </td>
                    <td>
                        <div class="text-white small"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                        <div class="text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($order['created_at'])) ?></div>
                    </td>
                    <td class="text-end">
                        <a href="<?= site_url('/admin/orders/' . $order['id']) ?>" class="btn btn-soft btn-sm px-3">
                            Details
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">No orders found yet.</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
</script>

<?= $this->endSection() ?>
