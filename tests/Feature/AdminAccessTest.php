<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * RBAC sanity checks for the /admin area (B16).
 */
class AdminAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testGuestRedirectedFromAdmin(): void
    {
        $result = $this->get('/admin');
        $result->assertRedirectTo('/login');
    }

    public function testStaffCanReachDashboard(): void
    {
        session()->set([
            'user_id'      => 999,
            'user_email'   => 'staff@nexgear.test',
            'user_name'    => 'Staff',
            'role'         => 'staff',
            'is_logged_in' => true,
        ]);

        $result = $this->get('/admin');
        $result->assertOK();
    }

    public function testStaffCannotReachAdminOnlyPaths(): void
    {
        session()->set([
            'user_id'      => 999,
            'user_email'   => 'staff@nexgear.test',
            'user_name'    => 'Staff',
            'role'         => 'staff',
            'is_logged_in' => true,
        ]);

        // Audit log is admin-only
        $result = $this->get('/admin/audit');
        $result->assertRedirect();
    }

    public function testRegularUserBouncedFromAdmin(): void
    {
        session()->set([
            'user_id'      => 1000,
            'user_email'   => 'user@nexgear.test',
            'user_name'    => 'User',
            'role'         => 'user',
            'is_logged_in' => true,
        ]);

        $result = $this->get('/admin');
        $result->assertRedirect();
    }
}
