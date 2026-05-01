<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- NuPhy Style Hero -->
<section class="vp-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="hero-sub">Gaming Collection</span>
                <h1 class="display-nuphy text-white">Hypernex <br> Field75</h1>
                <p class="hero-copy">Engineered for a long term gaming experience, not a disposable setup upgrade. Precision, speed, and soul in every keystroke.</p>
                <div class="hero-price">Rp 2.899.000</div>
                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-primary-glow px-5" href="<?= base_url('/collection') ?>">
                        Add to Cart <i class="bi bi-arrow-up-right ms-2"></i>
                    </a>
                    <a class="btn btn-soft px-5" href="<?= base_url('/collection') ?>">
                        Buy Now <i class="bi bi-arrow-up-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="zoom-in">
                <div class="hero-image-main">
                    <img src="https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop" alt="NuPhy Style Keyboard" class="img-fluid floating-anim">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="container py-10">
    <div class="text-center mb-10" data-aos="fade-up">
        <p class="hero-sub">Our Top Picks</p>
        <h2 class="display-nuphy fs-1">Precision Engineering for <br> Peak Performance</h2>
    </div>
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="0">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-broadcast"></i></div>
                <h3>Instant Connection</h3>
                <p>Seamlessly connects across all your devices. Instant pairing, no complicated setup. Immerse yourself in your work.</p>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-controller"></i></div>
                <h3>Intuitive Control</h3>
                <p>The integrated control knob delivers tactile precision, empowering you to fine-tune, explore, and craft without losing focus.</p>
            </div>
        </div>
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-layers"></i></div>
                <h3>Adaptable Design</h3>
                <p>Craft a personalized workspace with built custom keys, shortcuts, and layouts. Design a setup that’s as dynamic as your imagination.</p>
            </div>
        </div>
    </div>
</section>

<!-- Collection Section -->
<section class="container py-10" id="collection">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <p class="hero-sub">Explore Store</p>
            <h2 class="text-white h1 fw-bold">Gaming Masterpieces</h2>
        </div>
        <a href="<?= base_url('/collection') ?>" class="text-primary text-decoration-none fw-bold">View All <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <?= view('products/_card', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- NuPhy CTA Masterpiece -->
<section class="nuphy-cta text-center">
    <div class="container" data-aos="zoom-in">
        <div class="cta-content">
            <h2 class="cta-title text-white">
                Let's create your <br>
                <span>next <i class="bi bi-arrow-right cta-arrow"></i> masterpiece</span>
            </h2>
            <a href="<?= base_url('/collection') ?>" class="btn btn-soft px-5 py-3">
                Explore Products <i class="bi bi-arrow-up-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<style>
    .py-10 { padding: 100px 0; }
    .mb-10 { margin-bottom: 80px; }
    
    .floating-anim {
        animation: floating 6s ease-in-out infinite;
    }
    
    @keyframes floating {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-30px); }
    }
</style>

<!-- AOS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 1000, once: true });
</script>

<?= $this->endSection() ?>
