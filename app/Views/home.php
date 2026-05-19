<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- ========== SECTION 1: HERO (100vh) ========== -->
<section class="position-relative w-100" style="height: 100vh; margin-top: -85px; border-bottom: 1px solid var(--border);">
    <!-- Background Image with Overlay -->
    <div style="position: absolute; inset: 0; z-index: 1;">
        <img src="https://images.unsplash.com/photo-1595225476474-87563907a212?q=80&w=1920&auto=format&fit=crop" alt="NexGear Keyboard Setup" style="width: 100%; height: 100%; object-fit: cover;">
        <!-- Dark Overlay for Text Visibility -->
        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.2) 40%, transparent 100%);"></div>
    </div>
    
    <!-- Content Overlay (Bottom Left) -->
    <div class="container-fluid px-4 px-lg-5 position-absolute w-100 hero-overlay" style="bottom: 8vh; z-index: 2;">
        <span class="hero-eyebrow">
            <span class="font-serif me-2">01 /</span> New Drop · Curated for the Daily Ritual
        </span>
        <h1 class="hero-title">
            Built for the<br>
            <span class="font-serif" style="text-transform: none; font-style: italic;">Modern</span> Workspace.
        </h1>
        <p class="hero-sub">
            Mechanical decks, low-latency rodents, and surround stages —
            all chosen, all built to outlast the upgrade cycle.
        </p>
        <div class="hero-actions">
            <a href="<?= base_url('/products') ?>" class="hero-btn hero-btn-primary">
                Explore Collection <span class="ms-2">→</span>
            </a>
            <a href="#collection" class="hero-btn hero-btn-secondary">
                Featured Below
            </a>
        </div>
    </div>
    
    <!-- Slide Indicators -->
    <div class="or-hero-indicators d-none d-lg-flex">
        <span class="active">01</span>
        <span>02</span>
        <span>03</span>
    </div>
    
    <!-- Scroll Text -->
    <div class="or-hero-scroll d-none d-lg-block">Scroll</div>
</section>

<!-- ========== SECTION 2: MARQUEE TICKER ========== -->
<div class="or-marquee">
    <div class="or-marquee-track">
        <span>✦ A Modern Expression of Precision</span>
        <span>✦ Crafted for the Daily Ritual</span>
        <span>✦ Engineered Without Compromise</span>
        <span>✦ A Modern Expression of Precision</span>
        <span>✦ Crafted for the Daily Ritual</span>
        <span>✦ Engineered Without Compromise</span>
        <span>✦ A Modern Expression of Precision</span>
        <span>✦ Crafted for the Daily Ritual</span>
        <span>✦ Engineered Without Compromise</span>
        <span>✦ A Modern Expression of Precision</span>
        <span>✦ Crafted for the Daily Ritual</span>
        <span>✦ Engineered Without Compromise</span>
    </div>
</div>

<!-- ========== SECTION 3: BRAND STORY SPLIT (Image | Text) ========== -->
<section class="or-split">
    <div class="or-split-media" data-aos="fade-right">
        <img src="https://images.unsplash.com/photo-1617096819670-6de2869bbd2e?q=80&w=1200&auto=format&fit=crop" alt="Black and silver keyboard">
    </div>
    <div class="or-split-content" data-aos="fade-left">
        <div class="or-split-eyebrow"><span class="font-serif me-2">01 /</span> About Us</div>
        <h2 style="font-size: clamp(1.8rem, 3vw, 2.5rem); font-weight: 500; line-height: 1.15; margin-bottom: 1.5rem; letter-spacing: -0.03em;">
            We build instruments<br>for the <span class="font-serif">modern</span> workspace.
        </h2>
        <p class="or-split-text">
            Every detail is a deliberate choice — from the click of each switch to the curve of every keycap. 
            NexGear exists for those who believe their tools should be as intentional as their craft.
        </p>
        <a href="<?= base_url('/collection') ?>" class="or-split-link">
            Learn More <span>→</span>
        </a>
    </div>
</section>

<!-- ========== SECTION 4: PRODUCT GRID ========== -->
<section class="container-fluid px-0 border-bottom border-dark" id="collection">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h2 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.2rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">
            <span class="font-serif me-2">02 /</span> Curated Store
        </h2>
        <a href="<?= base_url('/collection') ?>" class="text-dark text-decoration-none fw-bold text-uppercase" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.8rem; letter-spacing: 0.05em;">
            View All [<?= count($products) ?>]
        </a>
    </div>
    
    <div class="row g-0">
        <?php foreach ($products as $index => $product): ?>
            <div class="col-md-6 col-xl-4 <?= ($index % 3 !== 2) ? 'border-end border-dark' : '' ?> border-bottom border-dark">
                <?= view('products/_card', ['product' => $product]) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ========== SECTION 5: FULL-WIDTH LIFESTYLE IMAGE ========== -->
<div class="or-fullwidth-img">
    <img src="https://images.unsplash.com/photo-1546435770-a3e426bf472b?q=80&w=1920&auto=format&fit=crop" alt="Black wireless headphones between keyboard and mouse">
</div>

<!-- ========== SECTION 6: REVERSED BRAND SPLIT (Text | Image) ========== -->
<section class="or-split reversed">
    <div class="or-split-content" data-aos="fade-right">
        <div class="or-split-eyebrow"><span class="font-serif me-2">03 /</span> The Ritual</div>
        <h2 style="font-size: clamp(1.8rem, 3vw, 2.5rem); font-weight: 500; line-height: 1.15; margin-bottom: 1.5rem; letter-spacing: -0.03em;">
            Precision is a<br><span class="font-serif">daily</span> practice.
        </h2>
        <p class="or-split-text">
            A keyboard is more than a tool — it's a companion for every idea you bring to life. 
            We design for the ritual of creation, blending heritage craftsmanship with modern engineering.
        </p>
        <a href="<?= base_url('/collection') ?>" class="or-split-link">
            Shop Now <span>→</span>
        </a>
    </div>
    <div class="or-split-media" data-aos="fade-left">
        <img src="https://images.unsplash.com/photo-1541140532154-b024d705b90a?q=80&w=1200&auto=format&fit=crop" alt="Keyboard Workspace">
    </div>
</section>


<!-- ========== SECTION 7: SECOND MARQUEE ========== -->
<div class="or-marquee">
    <div class="or-marquee-track">
        <span>✦ NexGear</span>
        <span>✦ Tactile Mastery</span>
        <span>✦ Built to Last</span>
        <span>✦ NexGear</span>
        <span>✦ Tactile Mastery</span>
        <span>✦ Built to Last</span>
        <span>✦ NexGear</span>
        <span>✦ Tactile Mastery</span>
        <span>✦ Built to Last</span>
        <span>✦ NexGear</span>
        <span>✦ Tactile Mastery</span>
        <span>✦ Built to Last</span>
    </div>
</div>

<?= view('partials/recently_viewed') ?>


<?= $this->endSection() ?>
