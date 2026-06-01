<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ContentSecurityPolicy;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CSP that drops the stray empty report-only headers.
 *
 * CodeIgniter's CSP always initialises `Content-Security-Policy-Report-Only`
 * and `Reporting-Endpoints` in buildHeaders(), but only fills them when
 * report-only directives exist (we never use report-only mode). The result is
 * two empty headers shipped on every response.
 *
 * finalize() runs at Response::send() — after all "after" filters — so the
 * headers can't be removed from a filter, and they originate in PHP so a web
 * server `Header unset` is unreliable across SAPIs. Overriding finalize() here
 * removes them at the exact point they're built, which always works.
 */
class NexGearContentSecurityPolicy extends ContentSecurityPolicy
{
    public function finalize(ResponseInterface $response)
    {
        parent::finalize($response);

        foreach (['Content-Security-Policy-Report-Only', 'Reporting-Endpoints'] as $name) {
            if (trim($response->getHeaderLine($name)) === '') {
                $response->removeHeader($name);
            }
        }
    }
}
