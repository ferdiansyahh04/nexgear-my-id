<?php
/**
 * Legal page shell — shared layout for Privacy/Terms/Refund/Shipping.
 *
 * Variables:
 *   $title   (string) — page heading
 *   $eyebrow (string) — small label above the heading (e.g. "Legal / PRV")
 *   $updated (string) — ISO date string of last revision
 *   $body    (string) — pre-rendered HTML content (escape upstream is the
 *                       responsibility of the calling view since static legal
 *                       copy is curator-authored, not user-supplied)
 *   $company (array)  — company info: name, email, city, country, website
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="legal-shell">
    <header class="legal-header border-bottom border-dark">
        <div class="container-fluid px-4 px-lg-5 py-5">
            <div class="legal-eyebrow">
                <span class="font-serif me-2"><?= esc($eyebrow ?? 'Legal /') ?></span>
                Last updated <?= esc(date('d F Y', strtotime($updated ?? 'now'))) ?>
            </div>
            <h1 class="legal-title"><?= esc($title) ?></h1>
        </div>
    </header>

    <div class="container-fluid px-4 px-lg-5 py-5">
        <div class="row g-5">
            <article class="col-lg-8 legal-body">
                <?= $body ?? '' ?>
            </article>

            <aside class="col-lg-4 legal-aside">
                <div class="legal-aside-card">
                    <div class="legal-aside-eyebrow">Document Index</div>
                    <ul class="legal-aside-list">
                        <li><a href="<?= base_url('/privacy') ?>"
                               class="<?= str_ends_with(current_url(), '/privacy') ? 'is-active' : '' ?>">Privacy Policy</a></li>
                        <li><a href="<?= base_url('/terms') ?>"
                               class="<?= str_ends_with(current_url(), '/terms') ? 'is-active' : '' ?>">Terms of Service</a></li>
                        <li><a href="<?= base_url('/refund-policy') ?>"
                               class="<?= str_ends_with(current_url(), '/refund-policy') ? 'is-active' : '' ?>">Refund Policy</a></li>
                        <li><a href="<?= base_url('/shipping-policy') ?>"
                               class="<?= str_ends_with(current_url(), '/shipping-policy') ? 'is-active' : '' ?>">Shipping Policy</a></li>
                    </ul>
                </div>

                <div class="legal-aside-card mt-4">
                    <div class="legal-aside-eyebrow">Contact</div>
                    <p class="legal-aside-meta">
                        <strong><?= esc($company['name']) ?></strong><br>
                        <?= esc($company['city']) ?><br>
                        <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a>
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.legal-shell { background: var(--bg-dark); color: var(--text-primary); }
.legal-header {
    background: var(--bg-panel);
}
.legal-eyebrow {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--text-muted);
    font-weight: 700;
    margin-bottom: 1rem;
}
.legal-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    text-transform: uppercase;
    color: var(--primary);
    margin: 0;
}

.legal-body { font-size: 1rem; line-height: 1.8; color: var(--text-primary); }
.legal-body h2 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin-top: 3rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
    color: var(--primary);
}
.legal-body h2:first-child { margin-top: 0; }
.legal-body h3 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.05rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
    color: var(--primary);
}
.legal-body p, .legal-body li { color: var(--text-secondary); }
.legal-body ul, .legal-body ol { padding-left: 1.5rem; }
.legal-body li { margin-bottom: 0.5rem; }
.legal-body a { color: var(--primary); text-decoration: underline; text-underline-offset: 3px; }
.legal-body a:hover { opacity: 0.65; }
.legal-body strong { color: var(--primary); font-weight: 700; }
.legal-body table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    font-size: 0.9rem;
}
.legal-body th, .legal-body td {
    border: 1px solid var(--border);
    padding: 0.75rem 1rem;
    text-align: left;
    vertical-align: top;
}
.legal-body th {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    background: var(--bg-panel);
}

.legal-aside { position: sticky; top: 100px; height: fit-content; }
.legal-aside-card {
    border: 1px solid var(--border);
    background: var(--bg-panel);
    padding: 1.5rem;
}
.legal-aside-eyebrow {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--text-muted);
    font-weight: 700;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border);
}
.legal-aside-list { list-style: none; padding: 0; margin: 0; }
.legal-aside-list li { margin-bottom: 0.5rem; }
.legal-aside-list a {
    display: block;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-decoration: none;
    color: var(--text-secondary);
    padding: 0.5rem 0;
    border-bottom: 1px solid transparent;
    transition: all 0.2s ease;
}
.legal-aside-list a:hover { color: var(--primary); }
.legal-aside-list a.is-active {
    color: var(--primary);
    font-weight: 700;
    border-bottom-color: var(--primary);
}
.legal-aside-meta {
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--text-secondary);
    margin: 0;
}
.legal-aside-meta a { color: var(--primary); text-decoration: underline; text-underline-offset: 3px; }

@media (max-width: 991px) {
    .legal-aside { position: static; }
}
</style>
<?= $this->endSection() ?>
