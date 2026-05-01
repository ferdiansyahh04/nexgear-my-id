<?php

namespace App\Controllers;

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

        return redirect()->to('/products')->with('success', 'Account created. Welcome to Hypernex.');
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

        $this->loginSession((int) $user['id'], $user['name'], $user['email'], $user['role']);

        return redirect()->to($user['role'] === 'admin' ? '/admin/products' : '/products')
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
    }
}
