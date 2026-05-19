<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Smoke test for the admin dashboard. The dashboard runs ~8 aggregate
 * queries on app load — this test catches Model-API mistakes (e.g.
 * accidentally calling Query-Builder-only methods like whereNull()).
 */
class AdminDashboardTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testAdminCanLoadDashboard(): void
    {
        session()->set([
            'user_id'      => 1,
            'user_email'   => 'admin@nexgear.test',
            'user_name'    => 'Admin',
            'role'         => 'admin',
            'is_logged_in' => true,
        ]);

        $result = $this->get('/admin');
        $result->assertOK();
    }

    public function testStaffCanLoadDashboard(): void
    {
        session()->set([
            'user_id'      => 2,
            'user_email'   => 'staff@nexgear.test',
            'user_name'    => 'Staff',
            'role'         => 'staff',
            'is_logged_in' => true,
        ]);

        $result = $this->get('/admin');
        $result->assertOK();
    }

    public function testAdminCanLoadReports(): void
    {
        session()->set([
            'user_id'      => 1,
            'user_email'   => 'admin@nexgear.test',
            'user_name'    => 'Admin',
            'role'         => 'admin',
            'is_logged_in' => true,
        ]);

        $result = $this->get('/admin/reports');
        $result->assertOK();
    }

    public function testAdminCanLoadAuditLog(): void
    {
        session()->set([
            'user_id'      => 1,
            'user_email'   => 'admin@nexgear.test',
            'user_name'    => 'Admin',
            'role'         => 'admin',
            'is_logged_in' => true,
        ]);

        $result = $this->get('/admin/audit');
        $result->assertOK();
    }
}
