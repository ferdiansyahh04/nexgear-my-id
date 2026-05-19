<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="container-fluid px-0 border-bottom border-dark">
    <div class="d-flex justify-content-between align-items-center px-4 px-lg-5 py-4 border-bottom border-dark">
        <h1 class="m-0" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700;">
            <span class="font-serif me-2">AB /</span> Address Book
        </h1>
        <div class="d-flex gap-3">
            <a href="<?= base_url('/account/orders') ?>" class="account-nav-link" style="font-size: 0.7rem;">Orders <span>→</span></a>
            <a href="<?= base_url('/account/wishlist') ?>" class="account-nav-link" style="font-size: 0.7rem;">Wishlist <span>→</span></a>
        </div>
    </div>

    <div class="row g-0">
        <!-- List -->
        <div class="col-lg-7 border-end-lg border-dark p-4 p-lg-5">
            <?php if ($addresses === []): ?>
                <p class="font-serif italic text-muted">No saved addresses yet.</p>
            <?php else: ?>
                <?php foreach ($addresses as $addr): ?>
                    <article class="address-card">
                        <header class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.05em;">
                                    <?= esc($addr['label'] ?: 'Untitled') ?>
                                </span>
                                <?php if ((int) $addr['is_default'] === 1): ?>
                                    <span class="status-pill status-tone-success ms-2">Default</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-outline-dark btn-sm rounded-0"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#addr-edit-<?= (int) $addr['id'] ?>"
                                        aria-label="Edit"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="<?= base_url('/account/addresses/' . (int) $addr['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Remove this address?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-0" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </header>
                        <div class="font-serif" style="font-size: 1rem; line-height: 1.6;">
                            <strong><?= esc($addr['name']) ?></strong><br>
                            <span class="text-muted"><?= esc($addr['phone']) ?></span><br>
                            <?= nl2br(esc($addr['address'])) ?><br>
                            <?= esc($addr['city']) ?>, <?= esc($addr['postal_code']) ?>
                        </div>

                        <div class="collapse mt-3" id="addr-edit-<?= (int) $addr['id'] ?>">
                            <form action="<?= base_url('/account/addresses/' . (int) $addr['id']) ?>" method="post" class="row g-2">
                                <?= csrf_field() ?>
                                <div class="col-md-6">
                                    <input type="text" name="label" value="<?= esc($addr['label']) ?>" class="filter-price-input w-100" placeholder="Label (Home, Office...)">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="name" value="<?= esc($addr['name']) ?>" class="filter-price-input w-100" required placeholder="Recipient name">
                                </div>
                                <div class="col-md-6">
                                    <input type="tel" name="phone" value="<?= esc($addr['phone']) ?>" class="filter-price-input w-100" required placeholder="Phone">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="postal_code" value="<?= esc($addr['postal_code']) ?>" class="filter-price-input w-100" required placeholder="Postal code">
                                </div>
                                <div class="col-12">
                                    <textarea name="address" rows="2" class="filter-price-input w-100" required placeholder="Address"><?= esc($addr['address']) ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="city" value="<?= esc($addr['city']) ?>" class="filter-price-input w-100" required placeholder="City">
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label class="d-inline-flex align-items-center gap-2">
                                        <input type="checkbox" name="is_default" value="1" <?= (int) $addr['is_default'] === 1 ? 'checked' : '' ?>>
                                        <span class="text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">Set as default</span>
                                    </label>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-dark px-4 py-2 rounded-0 text-uppercase fw-bold"
                                            style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Add new form -->
        <div class="col-lg-5 p-4 p-lg-5">
            <h2 class="text-uppercase fw-bold mb-4" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                Add New Address
            </h2>
            <form action="<?= base_url('/account/addresses') ?>" method="post" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-md-12">
                    <input type="text" name="label" class="filter-price-input w-100" placeholder="Label (Home, Office...)">
                </div>
                <div class="col-md-12">
                    <input type="text" name="name" class="filter-price-input w-100" required placeholder="Full name" value="<?= esc(session('user_name')) ?>">
                </div>
                <div class="col-md-6">
                    <input type="tel" name="phone" class="filter-price-input w-100" required placeholder="Phone (+62...)">
                </div>
                <div class="col-md-6">
                    <input type="text" name="postal_code" class="filter-price-input w-100" required placeholder="Postal code">
                </div>
                <div class="col-12">
                    <textarea name="address" rows="3" class="filter-price-input w-100" required placeholder="Street, building, house number..."></textarea>
                </div>
                <div class="col-12">
                    <input type="text" name="city" class="filter-price-input w-100" required placeholder="City">
                </div>
                <div class="col-12">
                    <label class="d-inline-flex align-items-center gap-2">
                        <input type="checkbox" name="is_default" value="1">
                        <span class="text-uppercase fw-bold" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">Set as default</span>
                    </label>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-dark w-100 py-2 rounded-0 text-uppercase fw-bold"
                            style="font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; letter-spacing: 0.1em;">
                        Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
