<?php

namespace App\Controllers;

use App\Libraries\WishlistService;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function registerForm()
    {
        return view('auth/register', ['title' => 'Create Account']);
    }

    public function register()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users = new UserModel();
        $id = $users->insert([
            'name' => trim((string) $this->request->getPost('name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => 'user',
        ], true);

        $this->loginSession((int) $id, (string) $this->request->getPost('name'), (string) $this->request->getPost('email'), 'user');

        return redirect()->to('/products')->with('success', 'Account created. Welcome to NexGear.');
    }

    public function loginForm()
    {
        return view('auth/login', ['title' => 'Sign In']);
    }

    public function login()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = (new UserModel())
            ->where('email', strtolower(trim((string) $this->request->getPost('email'))))
            ->first();

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // 2FA gate — challenge before issuing the session.
        if ((int) ($user['totp_enabled'] ?? 0) === 1) {
            session()->set([
                'tfa_user_id' => (int) $user['id'],
                'tfa_started_at' => time(),
            ]);
            return redirect()->to('/login/2fa');
        }

        $this->loginSession((int) $user['id'], $user['name'], $user['email'], $user['role']);

        return redirect()->to($user['role'] === 'admin' || $user['role'] === 'staff' ? '/admin' : '/products')
            ->with('success', 'Signed in.');
    }

    public function twoFactorForm()
    {
        if (! session('tfa_user_id')) {
            return redirect()->to('/login');
        }
        return view('auth/two_factor', ['title' => '2FA Verification']);
    }

    public function twoFactorVerify()
    {
        $userId = (int) session('tfa_user_id');
        if ($userId < 1) return redirect()->to('/login');

        // Bound the challenge window — 5 minutes
        if (time() - (int) session('tfa_started_at') > 300) {
            session()->remove('tfa_user_id');
            session()->remove('tfa_started_at');
            return redirect()->to('/login')->with('error', '2FA challenge expired. Sign in again.');
        }

        $code = (string) $this->request->getPost('code');
        $user = (new UserModel())->find($userId);
        if (! $user) {
            return redirect()->to('/login')->with('error', 'Account not found.');
        }

        if (! (new \App\Libraries\TotpService())->verify((string) $user['totp_secret'], $code)) {
            return redirect()->back()->with('error', 'Invalid 2FA code. Try again.');
        }

        session()->remove('tfa_user_id');
        session()->remove('tfa_started_at');

        $this->loginSession((int) $user['id'], $user['name'], $user['email'], $user['role']);

        return redirect()->to($user['role'] === 'admin' || $user['role'] === 'staff' ? '/admin' : '/products')
            ->with('success', 'Signed in.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('success', 'Signed out.');
    }

    private function loginSession(int $id, string $name, string $email, string $role): void
    {
        session()->regenerate(true);
        session()->set([
            'user_id' => $id,
            'user_name' => $name,
            'user_email' => $email,
            'role' => $role,
            'is_logged_in' => true,
        ]);

        // B3: Lift any guest wishlist picks into the persistent table.
        (new WishlistService())->mergeGuestIntoUser($id);
    }
}
