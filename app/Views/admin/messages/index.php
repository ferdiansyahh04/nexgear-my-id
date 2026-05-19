<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="mb-4 d-flex gap-2 flex-wrap">
    <?php
    $statusOptions = ['' => 'All', 'new' => 'New', 'read' => 'Read', 'archived' => 'Archived'];
    foreach ($statusOptions as $key => $label):
    ?>
        <a href="<?= base_url('/admin/messages' . ($key !== '' ? '?status=' . $key : '')) ?>"
           class="filter-chip <?= $filter === $key ? 'is-active' : '' ?>">
            <?= esc($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="admin-table-wrap">
    <div class="p-4 border-bottom border-dark d-flex justify-content-between align-items-center bg-white">
        <h2 class="h6 mb-0 text-dark fw-bold text-uppercase" style="letter-spacing: 0.1em;">
            Inbox <?= $filter !== '' ? '— ' . esc(ucfirst($filter)) : '' ?>
        </h2>
        <span class="text-muted text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.15em;">
            <?= count($messages) ?> shown
        </span>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($messages === []): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted font-serif italic">No messages.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg):
                        $tone = $msg['status'] === 'new' ? 'info' : ($msg['status'] === 'archived' ? 'muted' : 'success');
                    ?>
                        <tr style="<?= $msg['status'] === 'new' ? 'font-weight: 700;' : '' ?>">
                            <td>
                                <div class="text-dark fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;"><?= esc($msg['name']) ?></div>
                                <div class="text-muted small font-serif italic"><?= esc($msg['email']) ?></div>
                            </td>
                            <td>
                                <div class="text-dark" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem;">
                                    <?= esc($msg['subject'] ?: '— no subject —') ?>
                                </div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    <?= esc(mb_strimwidth($msg['message'], 0, 80, '…')) ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-pill status-tone-<?= $tone ?>"><?= esc(ucfirst($msg['status'])) ?></span>
                            </td>
                            <td>
                                <div class="text-dark fw-bold" style="font-size: 0.8rem;"><?= date('d M Y', strtotime($msg['created_at'])) ?></div>
                                <div class="text-muted font-serif italic" style="font-size: 0.7rem;"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('/admin/messages/' . (int) $msg['id']) ?>" class="btn btn-dark btn-sm rounded-0 px-3 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.1em;">
                                    Open
                                </a>
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
