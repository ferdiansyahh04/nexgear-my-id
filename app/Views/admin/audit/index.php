<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<!-- Filters -->
<div class="admin-table-wrap p-4 mb-4">
    <form method="get" action="<?= site_url('/admin/audit') ?>" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Action</label>
            <input list="audit-actions" type="text" name="action" value="<?= esc($action) ?>"
                   class="form-control admin-input" placeholder="e.g. product.update">
            <datalist id="audit-actions">
                <?php foreach ($actions as $a): ?>
                    <option value="<?= esc($a) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>
        <div class="col-md-4">
            <label class="font-serif text-muted small text-uppercase mb-2 d-block italic" style="letter-spacing: 0.1em;">Actor (email or label)</label>
            <input type="text" name="actor" value="<?= esc($actor) ?>" class="form-control admin-input" placeholder="admin@nexgear.test">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-dark py-2 px-4 rounded-0 text-uppercase fw-bold me-1"
                    style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                Filter
            </button>
            <a href="<?= site_url('/admin/audit') ?>" class="btn btn-outline-dark py-2 px-4 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="admin-table-wrap">
    <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0 text-dark fw-bold text-uppercase" style="letter-spacing: 0.1em;">Audit Trail</h2>
        <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            <?= count($logs) ?> entries on this page
        </span>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Meta</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs === []): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted font-serif italic">No audit entries match those filters.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $row):
                        $meta = $row['meta'] !== null ? json_decode($row['meta'], true) : null;
                        // Color the action by its prefix
                        $prefix = strstr((string) $row['action'], '.', true);
                        $tone = match ($prefix) {
                            'product', 'category', 'product_image' => 'info',
                            'order'                                => 'success',
                            'coupon', 'message'                    => 'warning',
                            'stock', 'cart'                        => 'muted',
                            default                                => 'muted',
                        };
                    ?>
                        <tr>
                            <td>
                                <div class="text-dark fw-bold" style="font-size: 0.8rem;"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                                <div class="text-muted font-serif italic" style="font-size: 0.7rem;"><?= date('H:i:s', strtotime($row['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem;">
                                    <?= esc($row['actor_label'] ?: '— system —') ?>
                                </div>
                                <?php if ($row['user_id']): ?>
                                    <div class="text-muted font-serif italic" style="font-size: 0.7rem;">user #<?= (int) $row['user_id'] ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-pill status-tone-<?= $tone ?>"><?= esc($row['action']) ?></span></td>
                            <td>
                                <?php if ($row['target_type']): ?>
                                    <span class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem;">
                                        <?= esc($row['target_type']) ?>
                                        <?php if ($row['target_id']): ?>
                                            <span class="font-serif italic" style="font-weight: 400;">#<?= (int) $row['target_id'] ?></span>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($meta && is_array($meta)): ?>
                                    <details>
                                        <summary class="text-decoration-underline" style="cursor: pointer; font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.05em;">
                                            <?= count($meta) ?> field<?= count($meta) === 1 ? '' : 's' ?>
                                        </summary>
                                        <pre class="audit-meta-pre"><?= esc(json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                    </details>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code class="font-monospace small"><?= esc($row['ip_address'] ?: '—') ?></code>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pager && $pager->getPageCount() > 1): ?>
    <div class="mt-4 d-flex justify-content-center">
        <?= $pager->links('default', 'default_full') ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
