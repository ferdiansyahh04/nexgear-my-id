<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">CT /</span> Contact Us
        </h1>
    </div>

    <div class="row g-0">
        <!-- Info column -->
        <div class="col-lg-5 border-end-lg border-dark p-4 p-lg-5">
            <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; line-height: 1.05; letter-spacing: -0.03em; text-transform: uppercase; margin-bottom: 1.5rem;">
                Talk to the<br><span class="font-serif" style="text-transform: none;">team</span>.
            </h2>
            <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-secondary); margin-bottom: 2.5rem;">
                Questions about a product, a recent order, or partnerships?
                Drop a note below — we read every message.
            </p>

            <ul class="list-unstyled" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; line-height: 2;">
                <li>
                    <span class="text-muted text-uppercase fw-bold me-2" style="font-size: 0.65rem; letter-spacing: 0.15em;">Email</span>
                    <a href="mailto:hello@nexgear.my.id" class="text-dark text-decoration-none">hello@nexgear.my.id</a>
                </li>
                <li>
                    <span class="text-muted text-uppercase fw-bold me-2" style="font-size: 0.65rem; letter-spacing: 0.15em;">Hours</span>
                    Mon–Fri · 09:00–17:00 WIB
                </li>
                <li>
                    <span class="text-muted text-uppercase fw-bold me-2" style="font-size: 0.65rem; letter-spacing: 0.15em;">Response</span>
                    Within 1–2 business days
                </li>
            </ul>
        </div>

        <!-- Form column -->
        <div class="col-lg-7 p-4 p-lg-5">
            <form action="<?= base_url('/contact') ?>" method="post" data-validate novalidate>
                <?= csrf_field() ?>

                <!-- Honeypot — hidden from real users -->
                <div style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true">
                    <label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="row g-3">
                    <div class="col-md-6" data-field>
                        <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Name</label>
                        <input type="text" name="name" class="filter-price-input w-100" required minlength="2" maxlength="120"
                               value="<?= esc(old('name', session('user_name') ?? '')) ?>" data-rule="min:2">
                        <div class="field-error" data-error></div>
                    </div>
                    <div class="col-md-6" data-field>
                        <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Email</label>
                        <input type="email" name="email" class="filter-price-input w-100" required maxlength="160"
                               value="<?= esc(old('email', session('user_email') ?? '')) ?>" data-rule="email">
                        <div class="field-error" data-error></div>
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Subject (optional)</label>
                        <input type="text" name="subject" class="filter-price-input w-100" maxlength="160" value="<?= esc(old('subject', '')) ?>">
                    </div>
                    <div class="col-md-12" data-field>
                        <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.65rem; letter-spacing: 0.12em;">Message</label>
                        <textarea name="message" rows="6" class="filter-price-input w-100" required minlength="10" maxlength="2000" data-rule="min:10"><?= esc(old('message', '')) ?></textarea>
                        <div class="field-error" data-error></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-3 mt-4 rounded-0 text-uppercase fw-bold d-flex justify-content-between px-4"
                        style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                    <span>Send Message</span><span>→</span>
                </button>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
