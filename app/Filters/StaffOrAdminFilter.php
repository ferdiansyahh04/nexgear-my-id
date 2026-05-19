<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Allow either `admin` or `staff` to enter the route.
 *
 * Use case: read-only admin areas (orders list, messages inbox, reports
 * dashboard, audit log). Pair with the stricter `admin` filter for
 * mutations.
 */
class StaffOrAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Please sign in first.');
        }

        $role = session('role');
        if ($role !== 'admin' && $role !== 'staff') {
            return redirect()->to('/products')->with('error', 'Restricted area.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
