<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-table-wrap p-4 p-lg-5">
            <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="h4 m-0" style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; letter-spacing: -0.02em;">
                        <?= esc($message['subject'] ?: '— no subject —') ?>
                    </h2>
                    <div class="text-muted font-serif italic mt-2" style="font-size: 0.95rem;">
                        From <strong><?= esc($message['name']) ?></strong>
                        &lt;<a href="mailto:<?= esc($message['email']) ?>" class="text-dark"><?= esc($message['email']) ?></a>&gt;
                        · <?= date('d M Y, H:i', strtotime($message['created_at'])) ?>
                    </div>
                </div>
                <a href="<?= site_url('/admin/messages') ?>" class="btn btn-outline-dark btn-sm rounded-0 px-4 py-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">
                    ← Back
                </a>
            </header>

            <div class="message-body" style="font-size: 1rem; line-height: 1.7; color: var(--primary); white-space: pre-wrap;">
                <?= nl2br(esc($message['message'])) ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-table-wrap p-4">
            <h3 class="font-serif text-muted small text-uppercase mb-3 italic" style="letter-spacing: 0.1em;">Status</h3>
            <form action="<?= site_url('/admin/messages/' . (int) $message['id'] . '/status') ?>" method="post" class="d-grid gap-2">
                <?= csrf_field() ?>
                <?php foreach (['read' => 'Mark as Read', 'archived' => 'Archive', 'new' => 'Mark as New'] as $key => $label): ?>
                    <button type="submit" name="status" value="<?= $key ?>"
                            class="btn <?= $message['status'] === $key ? 'btn-dark' : 'btn-outline-dark' ?> py-2 rounded-0 text-uppercase fw-bold"
                            style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                        <?= esc($label) ?>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>

        <div class="admin-table-wrap p-4 mt-4">
            <h3 class="font-serif text-muted small text-uppercase mb-3 italic" style="letter-spacing: 0.1em;">Reply</h3>
            <a href="mailto:<?= esc($message['email']) ?>?subject=<?= esc(rawurlencode('Re: ' . ($message['subject'] ?: 'Your message'))) ?>"
               class="btn btn-dark w-100 py-2 rounded-0 text-uppercase fw-bold"
               style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                <i class="bi bi-envelope-fill me-2"></i>Open Mail Client
            </a>
            <p class="text-muted font-serif italic small mt-3 mb-0" style="font-size: 0.8rem;">
                IP: <?= esc($message['ip_address'] ?: '—') ?>
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
