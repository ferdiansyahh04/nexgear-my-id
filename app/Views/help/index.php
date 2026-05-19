<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="px-4 px-lg-5 py-4 border-bottom border-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">HF /</span> Help &amp; FAQ
        </h1>
        <a href="<?= base_url('/contact') ?>" class="account-nav-link" style="font-size: 0.7rem;">
            Still need help? <span>→</span>
        </a>
    </div>

    <div class="px-4 px-lg-5 py-5">
        <h2 class="mb-3" style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; line-height: 1.05; letter-spacing: -0.03em; text-transform: uppercase;">
            How can we<br><span class="font-serif" style="text-transform: none; font-style: italic;">help you?</span>
        </h2>
        <p class="text-muted mb-5" style="font-size: 1.05rem; line-height: 1.7; max-width: 560px;">
            Quick answers to the questions we hear most often. Can't find what you're looking for?
            <a href="<?= base_url('/contact') ?>" class="text-dark fw-bold">Reach out</a> — we typically reply within one business day.
        </p>

        <!-- Category nav (anchor jumps) -->
        <div class="d-flex gap-2 flex-wrap mb-5">
            <?php foreach ($categories as $cat): ?>
                <a href="#<?= esc($cat['key']) ?>" class="filter-chip">
                    <?= esc($cat['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- FAQ groups -->
        <?php foreach ($categories as $cat): ?>
            <section id="<?= esc($cat['key']) ?>" class="faq-group">
                <h3 class="faq-group-title">
                    <?= esc($cat['title']) ?>
                </h3>
                <?php foreach ($cat['items'] as $i => $item): ?>
                    <details class="faq-item" <?= $i === 0 ? 'open' : '' ?>>
                        <summary class="faq-question">
                            <span><?= esc($item['q']) ?></span>
                            <span class="faq-arrow" aria-hidden="true">+</span>
                        </summary>
                        <div class="faq-answer">
                            <?= $item['a'] /* curated copy — allow inline tags like <a>, <strong> */ ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>

        <!-- Closing CTA -->
        <div class="faq-cta">
            <h3 class="text-uppercase fw-bold mb-2" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.5rem; letter-spacing: -0.02em;">
                Still have a question?
            </h3>
            <p class="text-muted font-serif italic mb-4">A real person reads every message.</p>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                <a href="<?= base_url('/contact') ?>" class="btn btn-dark px-5 py-3 rounded-0 text-uppercase fw-bold"
                   style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                    Contact Support →
                </a>
                <button type="button" class="btn btn-outline-dark px-4 py-3 rounded-0 text-uppercase fw-bold"
                        onclick="window.NexGear &amp;&amp; window.NexGear.showOnboarding &amp;&amp; window.NexGear.showOnboarding()"
                        style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                    Replay Welcome
                </button>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
