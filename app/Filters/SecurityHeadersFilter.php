<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Hardens response headers, complementing CodeIgniter's built-in
 * `secureheaders` filter and the CSP machinery:
 *
 *   1. Adds HSTS (Strict-Transport-Security). The stock SecureHeaders filter
 *      does NOT emit HSTS, and the Apache rule only fires when env=HTTPS — which
 *      is unreliable behind a TLS-terminating proxy (Cloudflare). We detect a
 *      secure request directly (incl. X-Forwarded-Proto) and set it ourselves.
 *
 *   2. Removes the empty `Content-Security-Policy-Report-Only` header that CI4
 *      always initialises but never fills when report-only mode is off, so it
 *      doesn't ship as a stray empty header.
 *
 * Registered as a global `after` filter in Config\Filters.
 */
class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No pre-processing.
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ── 1. HSTS over HTTPS (direct or proxied) ──────────────────────
        if ($this->isSecure($request)) {
            $response->setHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // ── 2. Drop the empty report-only CSP header (CI4 artifact) ─────
        $reportOnly = $response->getHeaderLine('Content-Security-Policy-Report-Only');
        if (trim($reportOnly) === '') {
            $response->removeHeader('Content-Security-Policy-Report-Only');
        }

        // Drop the matching empty Reporting-Endpoints header if present.
        $reportingEndpoints = $response->getHeaderLine('Reporting-Endpoints');
        if (trim($reportingEndpoints) === '') {
            $response->removeHeader('Reporting-Endpoints');
        }
    }

    /**
     * True when the request reached us over HTTPS, accounting for a
     * TLS-terminating reverse proxy / CDN (Cloudflare) that forwards plain
     * HTTP to the origin while signalling the original scheme via headers.
     */
    private function isSecure(RequestInterface $request): bool
    {
        // isSecure() lives on the concrete IncomingRequest, not the interface,
        // and already accounts for X-Forwarded-Proto / Front-End-Https.
        if (method_exists($request, 'isSecure') && $request->isSecure()) {
            return true;
        }

        $forwardedProto = strtolower(trim((string) $request->getHeaderLine('X-Forwarded-Proto')));
        if ($forwardedProto === 'https') {
            return true;
        }

        // Cloudflare also exposes the visitor scheme here.
        $cfVisitor = (string) $request->getHeaderLine('CF-Visitor');
        return $cfVisitor !== '' && str_contains($cfVisitor, '"scheme":"https"');
    }
}
