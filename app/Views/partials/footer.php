<footer class="nuphy-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h4 class="footer-heading">Navigation</h4>
                <ul class="footer-links">
                    <li><a href="<?= base_url('/products') ?>">Products</a></li>
                    <li><a href="<?= base_url('/collection') ?>">Store</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-heading">About</h4>
                <ul class="footer-links">
                    <li><a href="#">About</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Forum</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-heading">Social</h4>
                <ul class="footer-links">
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Instagram</a></li>
                    <li><a href="#">X - Twitter</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-heading">Get in Touch</h4>
                <ul class="footer-links">
                    <li><a href="mailto:hello@hypernex.test">hello@hypernex.test</a></li>
                </ul>
            </div>
            <div class="footer-newsletter">
                <h4 class="newsletter-title text-white">Join Our Community</h4>
                <p class="newsletter-copy">Get updates, tips, and early access to new releases.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your Email">
                    <button type="submit"><i class="bi bi-arrow-right-short"></i></button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-logo">Hypernex®</div>
            <div class="d-flex flex-column align-items-end gap-3">
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms & Conditions</a>
                </div>
                <div class="text-muted small">&copy; <?= date('Y') ?> Hypernex, Inc. All rights reserved</div>
            </div>
        </div>
    </div>
</footer>
