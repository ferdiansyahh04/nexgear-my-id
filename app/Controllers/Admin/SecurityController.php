<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogService;
use App\Libraries\TotpService;
use App\Models\UserModel;

/**
 * Per-user 2FA management. Staff and admins both see this — each manages
 * their own TOTP secret only.
 */
class SecurityController extends BaseController
{
    public function index()
    {
        $user = $this->currentUser();

        $tfaPending = session('tfa_pending_secret');
        $qr = null;
        if ((int) ($user['totp_enabled'] ?? 0) !== 1 && $tfaPending) {
            $qr = (new TotpService())->qrDataUri($user['email'], $tfaPending);
        }

        return view('admin/security/index', [
            'title'          => 'Security',
            'user'           => $user,
            'pendingSecret'  => $tfaPending,
            'pendingQr'      => $qr,
        ]);
    }

    /**
     * Generate a fresh secret and store in session until the user confirms
     * with a code from their authenticator app.
     */
    public function setupStart()
    {
        $user = $this->currentUser();
        if ((int) ($user['totp_enabled'] ?? 0) === 1) {
            return redirect()->to('/admin/security')->with('error', '2FA is already enabled.');
        }

        $secret = (new TotpService())->newSecret();
        session()->set('tfa_pending_secret', $secret);

        return redirect()->to('/admin/security');
    }

    public function setupConfirm()
    {
        $user   = $this->currentUser();
        $secret = (string) session('tfa_pending_secret');
        $code   = (string) $this->request->getPost('code');

        if ($secret === '') {
            return redirect()->to('/admin/security')->with('error', 'Setup expired. Try again.');
        }

        if (! (new TotpService())->verify($secret, $code)) {
            return redirect()->to('/admin/security')->with('error', 'Invalid code. Check your authenticator app and retry.');
        }

        (new UserModel())->update($user['id'], [
            'totp_secret'  => $secret,
            'totp_enabled' => 1,
        ]);
        session()->remove('tfa_pending_secret');

        (new AuditLogService())->log('security.totp_enabled', [
            'target_type' => 'user',
            'target_id'   => (int) $user['id'],
        ]);

        return redirect()->to('/admin/security')->with('success', 'Two-factor authentication enabled.');
    }

    public function disable()
    {
        $user = $this->currentUser();
        $code = (string) $this->request->getPost('code');

        if ((int) ($user['totp_enabled'] ?? 0) !== 1) {
            return redirect()->to('/admin/security')->with('error', '2FA is not enabled.');
        }

        if (! (new TotpService())->verify((string) $user['totp_secret'], $code)) {
            return redirect()->to('/admin/security')->with('error', 'Invalid code. Cannot disable 2FA without a valid code.');
        }

        (new UserModel())->update($user['id'], [
            'totp_secret'  => null,
            'totp_enabled' => 0,
        ]);

        (new AuditLogService())->log('security.totp_disabled', [
            'target_type' => 'user',
            'target_id'   => (int) $user['id'],
        ]);

        return redirect()->to('/admin/security')->with('success', 'Two-factor authentication disabled.');
    }

    private function currentUser(): array
    {
        $user = (new UserModel())->find((int) session('user_id'));
        if (! $user) {
            // Fallback so the view doesn't crash; should never happen behind staff filter.
            return ['id' => 0, 'email' => '', 'totp_enabled' => 0];
        }
        return $user;
    }
}
