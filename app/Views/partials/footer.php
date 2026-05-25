<footer style="padding-top: 80px; padding-bottom: 40px; background-color: var(--bg-dark);">
    <div class="container-fluid px-4 px-lg-5">
        
        <!-- Brand Block -->
        <div class="text-center mb-5 pb-5 border-bottom border-dark">
            <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(3rem, 8vw, 6rem); font-weight: 700; line-height: 1; letter-spacing: -0.05em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;">NEXGEAR</h2>
            <p class="text-uppercase mb-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.15em; color: var(--text-muted);">A Modern Expression of Precision</p>
        </div>

        <!-- B11 — Newsletter Signup -->
        <div class="row g-0 align-items-center mb-5 pb-5 border-bottom border-dark">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="text-uppercase fw-bold mb-2" style="font-size: 0.7rem; letter-spacing: 0.15em; font-family: 'Space Grotesk', sans-serif; color: var(--text-muted);">Newsletter</div>
                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(1.4rem, 2.5vw, 2rem); font-weight: 700; letter-spacing: -0.02em; line-height: 1.15; margin: 0; color: var(--primary);">
                    Drops, restocks, and rare finds<br>
                    — straight to your inbox.
                </h3>
            </div>
            <div class="col-md-6">
                <form action="<?= base_url('/newsletter/subscribe') ?>" method="post" id="newsletterForm" class="footer-newsletter-form">
                    <?= csrf_field() ?>
                    <div class="d-flex">
                        <input type="email" name="email" class="footer-newsletter-input" required placeholder="Your email address" autocomplete="email">
                        <button type="submit" class="footer-newsletter-btn">
                            Subscribe <span class="ms-2">→</span>
                        </button>
                    </div>
                    <p class="footer-newsletter-hint mb-0 mt-2" data-newsletter-hint>
                        We will only send you the good stuff. Unsubscribe anytime.
                    </p>
                </form>
            </div>
        </div>
        
        <!-- 4-Column Grid (Matching Odd Ritual) -->
        <div class="row g-0">
            <div class="col-md-3 pe-md-4 mb-4 mb-md-0">
                <h4 class="text-uppercase fw-bold pb-3 mb-3 border-bottom border-dark" style="font-size: 0.7rem; letter-spacing: 0.15em; font-family: 'Space Grotesk', sans-serif;">Site Index</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= base_url('/collection') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Shop Now</a></li>
                    <li class="mb-2"><a href="<?= base_url('/') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Home</a></li>
                    <li class="mb-2"><a href="<?= base_url('/contact') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Contact Us</a></li>
                    <li class="mb-2"><a href="<?= base_url('/help') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Help &amp; FAQ</a></li>
                    <li class="mb-2"><a href="<?= base_url('/products/compare') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Compare</a></li>
                </ul>
            </div>
            <div class="col-md-3 pe-md-4 mb-4 mb-md-0">
                <h4 class="text-uppercase fw-bold pb-3 mb-3 border-bottom border-dark" style="font-size: 0.7rem; letter-spacing: 0.15em; font-family: 'Space Grotesk', sans-serif;">Account</h4>
                <ul class="list-unstyled">
                    <?php if (session('is_logged_in')): ?>
                        <li class="mb-2"><a href="<?= base_url('/account/orders') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Orders</a></li>
                        <li class="mb-2"><a href="<?= base_url('/account/wishlist') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Wishlist</a></li>
                        <li class="mb-2"><a href="<?= base_url('/account/addresses') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Addresses</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="<?= base_url('/login') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Sign In</a></li>
                        <li class="mb-2"><a href="<?= base_url('/register') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3 pe-md-4 mb-4 mb-md-0">
                <h4 class="text-uppercase fw-bold pb-3 mb-3 border-bottom border-dark" style="font-size: 0.7rem; letter-spacing: 0.15em; font-family: 'Space Grotesk', sans-serif;">Get in Touch</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="mailto:hello@nexgear.my.id" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">hello@nexgear.my.id</a></li>
                    <li class="mb-2"><a href="<?= base_url('/contact') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Send Message</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h4 class="text-uppercase fw-bold pb-3 mb-3 border-bottom border-dark" style="font-size: 0.7rem; letter-spacing: 0.15em; font-family: 'Space Grotesk', sans-serif;">Legal</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= base_url('/privacy') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Privacy Policy</a></li>
                    <li class="mb-2"><a href="<?= base_url('/refund-policy') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Refunds</a></li>
                    <li class="mb-2"><a href="<?= base_url('/shipping-policy') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Shipping</a></li>
                    <li class="mb-2"><a href="<?= base_url('/terms') ?>" class="text-dark text-decoration-none text-uppercase" style="font-size: 0.8rem; font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.05em;">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Bottom Row -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-5 pt-4 border-top border-dark">
            <div class="text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em; font-family: 'Space Grotesk', sans-serif;">
                All Rights Reserved _ NexGear&copy;<?= date('Y') ?>
            </div>
        </div>
    </div>
</footer>
