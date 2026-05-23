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

    /**
     * Change the signed-in user's password.
     *
     * Flow:
     *  1. Validate input (current + new + confirm)
     *  2. Verify current password against bcrypt hash
     *  3. Re-hash with PASSWORD_DEFAULT and persist
     *  4. Audit log (no plaintext, just a marker)
     *  5. Re-issue session ID to invalidate any captured cookies
     */
    public function changePassword()
    {
        $rules = [
            'current_password'     => 'required',
            'new_password'         => 'required|min_length[8]|max_length[128]',
            'new_password_confirm' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/admin/security')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $user = $this->currentUser();
        if ((int) ($user['id'] ?? 0) === 0) {
            return redirect()->to('/login')->with('error', 'Session expired.');
        }

        $current = (string) $this->request->getPost('current_password');
        $new     = (string) $this->request->getPost('new_password');

        if (! password_verify($current, (string) $user['password'])) {
            return redirect()->to('/admin/security')->with('error', 'Current password is incorrect.');
        }

        if (password_verify($new, (string) $user['password'])) {
            return redirect()->to('/admin/security')->with('error', 'New password must differ from the current one.');
        }

        (new UserModel())->update($user['id'], [
            'password' => password_hash($new, PASSWORD_DEFAULT),
        ]);

        (new AuditLogService())->log('security.password_changed', [
            'target_type' => 'user',
            'target_id'   => (int) $user['id'],
        ]);

        // Re-issue session id so any leaked session cookie is invalidated.
        session()->regenerate(true);

        return redirect()->to('/admin/security')->with('success', 'Password updated. Use the new one next time you sign in.');
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
