<?php
/**
 * Onboarding modal — first-visit welcome with category shortcuts.
 *
 * Visibility is gated client-side via localStorage flag `nexgear_onboarded`.
 * Emitted once per layout render; the JS module decides whether to show it.
 *
 * Variables (optional):
 *   $categories (array) — pre-fetched category rows; defaults to live query.
 */
$categories = $categories ?? [];
if ($categories === []) {
    try {
        $categories = (new \App\Models\CategoryModel())
            ->orderBy('sort_order', 'ASC')
            ->limit(6)
            ->find();
    } catch (\Throwable) {
        $categories = [];
    }
}

// Defense in depth: this partial is included by the global layout, so a
// controller that happens to pass an unrelated `$categories` (e.g. the Help
// page's FAQ structure) must not be able to 500 the whole site. Only keep
// rows that actually look like product-category records.
$categories = array_values(array_filter(
    is_array($categories) ? $categories : [],
    static fn ($cat) => is_array($cat) && isset($cat['id'], $cat['slug'], $cat['name'])
));
?>
<div class="onboarding-overlay" id="onboardingOverlay" hidden aria-hidden="true" role="dialog" aria-labelledby="onboardingTitle">
    <div class="onboarding-shell">
        <button type="button" class="onboarding-close" data-onboarding-dismiss aria-label="Close welcome">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="onboarding-header">
            <span class="onboarding-eyebrow">
                <span class="font-serif me-2">W /</span> Welcome to NexGear
            </span>
            <h2 id="onboardingTitle" class="onboarding-title">
                Built for the<br><span class="font-serif" style="text-transform: none; font-style: italic;">daily ritual</span>.
            </h2>
            <p class="onboarding-copy">
                A curated archive of mechanical keyboards, low-latency mice, surround headsets,
                and the small things that make every session better.
            </p>
        </div>

        <?php if ($categories !== []): ?>
            <div class="onboarding-section">
                <div class="onboarding-section-label">Pick a starting point</div>
                <div class="onboarding-categories">
                    <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
                        <a href="<?= base_url('/products?category=' . (int) $cat['id']) ?>"
                           class="onboarding-category"
                           data-onboarding-track="category-<?= esc($cat['slug']) ?>">
                            <span class="onboarding-category-name"><?= esc($cat['name']) ?></span>
                            <span class="onboarding-category-arrow">→</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="onboarding-section">
            <div class="onboarding-section-label">Or jump right in</div>
            <div class="onboarding-actions">
                <a href="<?= base_url('/collection') ?>" class="onboarding-btn onboarding-btn-primary">
                    Browse Everything <span class="ms-2">→</span>
                </a>
                <?php if (! session('is_logged_in')): ?>
                    <a href="<?= base_url('/register') ?>" class="onboarding-btn onboarding-btn-secondary">
                        Create Account
                    </a>
                <?php endif; ?>
                <button type="button" class="onboarding-btn onboarding-btn-ghost" data-onboarding-dismiss>
                    Skip
                </button>
            </div>
        </div>

        <div class="onboarding-footer">
            <span>Press <kbd class="onboarding-kbd">Esc</kbd> to close</span>
            <span class="font-serif italic">— You can revisit this later via Help</span>
        </div>
    </div>
</div>
