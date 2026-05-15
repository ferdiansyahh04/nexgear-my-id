<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Feature tests for public-facing routes.
 */
class RouteAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    // ── Public Routes (should be accessible) ────────────

    public function testHomepageReturns200(): void
    {
        $result = $this->get('/');
        $result->assertOK();
    }

    public function testProductListingReturns200(): void
    {
        $result = $this->get('/products');
        $result->assertOK();
    }

    public function testCollectionAliasReturns200(): void
    {
        $result = $this->get('/collection');
        $result->assertOK();
    }

    public function testLoginPageReturns200(): void
    {
        $result = $this->get('/login');
        $result->assertOK();
    }

    public function testRegisterPageReturns200(): void
    {
        $result = $this->get('/register');
        $result->assertOK();
    }

    // ── Protected Routes (should redirect) ──────────────

    public function testCheckoutRedirectsWithoutAuth(): void
    {
        $result = $this->get('/checkout');
        $result->assertRedirectTo('/login');
    }

    public function testAdminRedirectsWithoutAuth(): void
    {
        $result = $this->get('/admin/products');
        $result->assertRedirectTo('/login');
    }

    // ── Non-existent Routes ─────────────────────────────

    public function testNonExistentRouteReturns404(): void
    {
        $result = $this->get('/this-does-not-exist');
        $result->assertStatus(404);
    }
}
