<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Use our CSP subclass that strips the empty report-only headers CI4
     * otherwise ships on every response. Mirrors the core factory signature.
     */
    public static function csp(?\Config\ContentSecurityPolicy $config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('csp', $config);
        }

        $config ??= config(\Config\ContentSecurityPolicy::class);

        return new \App\Libraries\NexGearContentSecurityPolicy($config);
    }
}
