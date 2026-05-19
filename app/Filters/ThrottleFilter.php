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

        // Sanitize IP (IPv6 contains ":" which CodeIgniter cache treats as reserved)
        $ip  = preg_replace('/[^a-zA-Z0-9]/', '_', (string) $request->getIPAddress());
        $key = 'throttle_' . $ip . '_' . md5((string) current_url());

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
