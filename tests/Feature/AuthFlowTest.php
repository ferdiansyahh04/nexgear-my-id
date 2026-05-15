<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Feature tests for authentication flows.
 */
class AuthFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testLoginWithInvalidCredentialsRedirectsBack(): void
    {
        $result = $this->post('/login', [
            'email'    => 'invalid@test.com',
            'password' => 'wrongpassword',
        ]);

        $result->assertRedirect();
    }

    public function testLoginWithEmptyFieldsShowsValidationErrors(): void
    {
        $result = $this->post('/login', [
            'email'    => '',
            'password' => '',
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('errors');
    }

    public function testRegisterWithMismatchedPasswordsShowsError(): void
    {
        $result = $this->post('/register', [
            'name'             => 'Test User',
            'email'            => 'newuser@test.com',
            'password'         => 'password123',
            'password_confirm' => 'different123',
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('errors');
    }

    public function testRegisterWithShortPasswordShowsError(): void
    {
        $result = $this->post('/register', [
            'name'             => 'Test User',
            'email'            => 'newuser@test.com',
            'password'         => '123',
            'password_confirm' => '123',
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('errors');
    }

    public function testLogoutDestroysSession(): void
    {
        // Simulate logged-in state
        session()->set([
            'user_id'      => 1,
            'user_name'    => 'Test',
            'user_email'   => 'test@test.com',
            'role'         => 'user',
            'is_logged_in' => true,
        ]);

        $result = $this->post('/logout');
        $result->assertRedirect();
    }
}
