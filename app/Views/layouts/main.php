<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'NexGear Store') ?> | NexGear Store</title>
    <?= view('partials/seo_head', ['title' => $title ?? 'NexGear Store', 'product' => $product ?? null, 'aggregate' => $aggregate ?? null]) ?>

    <!-- PWA -->
    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <meta name="theme-color" content="#0d0d0d" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f2f2f2" media="(prefers-color-scheme: light)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.svg') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link href="<?= asset_url('assets/css/app.css') ?>" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>" id="csrf-token">
    <!-- A15: Set theme as early as possible to avoid FOUC -->
    <script {csp-script-nonce}>
        (function() {
            try {
                var saved = localStorage.getItem('nexgear_theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = saved || (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) { /* localStorage might be blocked */ }
        })();
    </script>
</head>
<body>
    <!-- A9 — page transition progress bar -->
    <div class="page-progress" id="pageProgress" aria-hidden="true"></div>

    <?= $this->include('partials/nav') ?>

    <main>
        <div class="container py-4">
            <?= $this->include('partials/flash') ?>
        </div>
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('partials/footer') ?>
    
    <!-- TOP-CRITICAL #3 — Mobile fixed bottom nav (visible <992px) -->
    <?= $this->include('partials/mobile_bottom_nav') ?>
    
    <!-- Custom Cursor -->
    <div id="customCursor" class="custom-cursor">VIEW</div>

    <!-- A10 — Compare Tray (floating, hidden when empty) -->
    <div class="compare-tray" id="compareTray" hidden aria-live="polite">
        <div class="compare-tray-inner">
            <span class="compare-tray-label">
                <i class="bi bi-bar-chart-steps me-2"></i>
                <span data-compare-count>0</span> / 3 to compare
            </span>
            <div class="compare-tray-thumbs" id="compareTrayThumbs"></div>
            <div class="compare-tray-actions">
                <button type="button" class="compare-tray-btn" id="compareTrayClear">Clear</button>
                <a href="<?= base_url('/products/compare') ?>" class="compare-tray-btn is-primary" id="compareTrayGo">
                    Compare <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- D4 — First-visit onboarding modal -->
    <?= $this->include('partials/onboarding_modal') ?>

    <!-- Quick View Modal Shell -->
    <div class="modal fade quick-view-modal" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close-quick" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="modal-body p-0" id="quickViewBody">
                    <div class="quick-view-skeleton">
                        <div class="qv-skel-media"></div>
                        <div class="qv-skel-body">
                            <div class="qv-skel-line w-25"></div>
                            <div class="qv-skel-line w-75"></div>
                            <div class="qv-skel-line w-50"></div>
                            <div class="qv-skel-line w-100 mt-4"></div>
                            <div class="qv-skel-line w-100"></div>
                            <div class="qv-skel-button"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script {csp-script-nonce}>AOS.init({ duration: 800, once: true, easing: 'ease-out-cubic' });</script>
    <script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
