<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$enabled = (int) ($user['totp_enabled'] ?? 0) === 1;
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="admin-table-wrap p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="h4 m-0" style="font-family: 'Space Grotesk', sans-serif; font-weight: 700;">Two-Factor Authentication</h2>
                    <p class="text-muted font-serif italic mt-2 mb-0" style="font-size: 0.95rem;">
                        Protect your account with a time-based one-time password (TOTP).
                    </p>
                </div>
                <span class="status-pill status-tone-<?= $enabled ? 'success' : 'muted' ?>" style="font-size: 0.85rem;">
                    <?= $enabled ? '✓ Enabled' : 'Disabled' ?>
                </span>
            </div>

            <?php if ($enabled): ?>
                <p style="font-size: 0.95rem; line-height: 1.7;">
                    2FA is currently active on your account. Each sign-in requires a 6-digit code
                    from your authenticator app.
                </p>

                <form action="<?= site_url('/admin/security/disable') ?>" method="post" class="mt-4">
                    <?= csrf_field() ?>
                    <h3 class="text-uppercase fw-bold mb-3" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                        Disable Two-Factor Authentication
                    </h3>
                    <p class="text-muted font-serif italic" style="font-size: 0.9rem;">
                        Enter your current 6-digit code to confirm.
                    </p>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="text" name="code" class="filter-price-input" required maxlength="6" pattern="[0-9]{6}"
                               placeholder="123456" inputmode="numeric" autocomplete="one-time-code"
                               style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.3em; font-size: 1.1rem; text-align: center; width: 180px;">
                        <button type="submit" class="btn btn-outline-danger px-4 py-2 rounded-0 text-uppercase fw-bold"
                                style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;"
                                onclick="return confirm('Disable 2FA? Your account will rely on password only.')">
                            Disable 2FA
                        </button>
                    </div>
                </form>

            <?php elseif ($pendingSecret): ?>
                <h3 class="text-uppercase fw-bold mb-3" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                    Step 2 — Verify
                </h3>
                <div class="row g-4 align-items-start">
                    <div class="col-md-5 text-center">
                        <div class="qr-wrap">
                            <img src="<?= esc($pendingQr) ?>" alt="Scan to add to authenticator" style="max-width: 220px; height: auto;">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <p style="font-size: 0.95rem; line-height: 1.7;">
                            Scan this QR with Google Authenticator, Authy, 1Password, or any TOTP app.
                            Then enter the 6-digit code below to finish.
                        </p>
                        <p class="text-muted small font-serif italic" style="word-break: break-all;">
                            Manual key:
                            <code class="font-monospace" style="font-size: 0.8rem;"><?= esc($pendingSecret) ?></code>
                        </p>

                        <form action="<?= site_url('/admin/security/setup/confirm') ?>" method="post" class="mt-3">
                            <?= csrf_field() ?>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <input type="text" name="code" class="filter-price-input" required maxlength="6" pattern="[0-9]{6}"
                                       placeholder="123456" inputmode="numeric" autocomplete="one-time-code"
                                       style="font-family: 'Space Grotesk', sans-serif; letter-spacing: 0.3em; font-size: 1.1rem; text-align: center; width: 180px;">
                                <button type="submit" class="btn btn-dark px-4 py-2 rounded-0 text-uppercase fw-bold"
                                        style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                                    Confirm Setup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <p style="font-size: 0.95rem; line-height: 1.7;">
                    With 2FA enabled, signing in will require a 6-digit code from your phone in addition to your password.
                    Strongly recommended for admin accounts.
                </p>
                <h3 class="text-uppercase fw-bold mt-4 mb-3" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; letter-spacing: 0.1em;">
                    Step 1 — Generate
                </h3>
                <form action="<?= site_url('/admin/security/setup/start') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-dark px-4 py-2 rounded-0 text-uppercase fw-bold"
                            style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                        Generate New Secret
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- ── Change Password ─────────────────────────────────────── -->
        <div class="admin-table-wrap p-4 p-lg-5 mt-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="h4 m-0" style="font-family: 'Space Grotesk', sans-serif; font-weight: 700;">Change Password</h2>
                    <p class="text-muted font-serif italic mt-2 mb-0" style="font-size: 0.95rem;">
                        Use a unique passphrase you don't reuse anywhere else. Minimum 8 characters.
                    </p>
                </div>
            </div>

            <form action="<?= site_url('/admin/security/password') ?>" method="post" autocomplete="off">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="text-uppercase fw-bold d-block mb-2" for="current_password"
                           style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.15em;">
                        Current Password
                    </label>
                    <input type="password" id="current_password" name="current_password" class="filter-price-input w-100"
                           required autocomplete="current-password"
                           style="font-family: 'Space Grotesk', sans-serif;">
                </div>

                <div class="mb-3">
                    <label class="text-uppercase fw-bold d-block mb-2" for="new_password"
                           style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.15em;">
                        New Password
                    </label>
                    <input type="password" id="new_password" name="new_password" class="filter-price-input w-100"
                           required minlength="8" maxlength="128" autocomplete="new-password"
                           style="font-family: 'Space Grotesk', sans-serif;">
                    <p class="text-muted font-serif italic mb-0 mt-1" style="font-size: 0.8rem;">
                        At least 8 characters. Mix letters, numbers, and a symbol if you can.
                    </p>
                </div>

                <div class="mb-4">
                    <label class="text-uppercase fw-bold d-block mb-2" for="new_password_confirm"
                           style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.15em;">
                        Confirm New Password
                    </label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm" class="filter-price-input w-100"
                           required minlength="8" maxlength="128" autocomplete="new-password"
                           style="font-family: 'Space Grotesk', sans-serif;">
                </div>

                <button type="submit" class="btn btn-dark px-4 py-2 rounded-0 text-uppercase fw-bold"
                        style="font-family: 'Space Grotesk', sans-serif; font-size: 0.7rem; letter-spacing: 0.1em;">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="admin-table-wrap p-4">
            <h3 class="font-serif text-muted small text-uppercase mb-3 italic" style="letter-spacing: 0.1em;">Recommended Apps</h3>
            <ul class="list-unstyled" style="font-family: 'Space Grotesk', sans-serif; font-size: 0.85rem; line-height: 2;">
                <li>· Google Authenticator</li>
                <li>· Microsoft Authenticator</li>
                <li>· Authy</li>
                <li>· 1Password</li>
                <li>· Raivo OTP / Aegis (FOSS)</li>
            </ul>
        </div>

        <div class="admin-table-wrap p-4 mt-4">
            <h3 class="font-serif text-muted small text-uppercase mb-3 italic" style="letter-spacing: 0.1em;">How It Works</h3>
            <ol class="ps-3" style="font-size: 0.9rem; line-height: 1.7;">
                <li class="mb-2">Scan the QR code with your authenticator app.</li>
                <li class="mb-2">Your app generates a new 6-digit code every 30 seconds.</li>
                <li class="mb-2">On every sign-in, enter your password then the current code.</li>
                <li>Lose your phone? Contact an admin to reset.</li>
            </ol>
        </div>
    </div>
</div>

<style>
.qr-wrap {
    border: 1px solid var(--border);
    background: #fff;
    padding: 16px;
    display: inline-block;
}
[data-theme="dark"] .qr-wrap { background: #fff; } /* keep QR white for scan reliability */
</style>
<?= $this->endSection() ?>
