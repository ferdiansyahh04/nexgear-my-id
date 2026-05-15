<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Throttle Filter — Rate-limits requests to prevent brute force attacks.
 *
 * Apply to login/register routes. Default: 5 attempts per 60 seconds per IP.
 */
class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        // Build a key based on IP + route path for granular limiting
        $key = 'throttle_' . $request->getIPAddress() . '_' . md5((string) current_url());

        // Allow 5 requests per 60 seconds
        if (! $throttler->check($key, 5, MINUTE)) {
            return Services::response()
                ->setStatusCode(429)
                ->setBody(view('errors/html/throttled'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
