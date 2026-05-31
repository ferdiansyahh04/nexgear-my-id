<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Adds HSTS (Strict-Transport-Security) to responses served over HTTPS.
 *
 * CodeIgniter's built-in `secureheaders` filter does NOT emit HSTS, and the
 * Apache rule is gated on env=HTTPS which is unreliable behind a
 * TLS-terminating proxy (Cloudflare forwards plain HTTP to the origin). We
 * detect a secure request directly — including the proxy's X-Forwarded-Proto /
 * CF-Visitor signals — and set the header ourselves.
 *
 * (The empty `Content-Security-Policy-Report-Only` header that CI4 emits is
 * stripped at the web-server layer in public/.htaccess, because CI4's CSP
 * finalize() runs at send() time — after this filter — and would re-add it.)
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
        if ($this->isSecure($request)) {
            $response->setHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
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
