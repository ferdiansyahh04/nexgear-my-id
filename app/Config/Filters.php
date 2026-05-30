<?php

namespace Config;

use App\Filters\AdminFilter;
use App\Filters\AuthFilter;
use App\Filters\StaffOrAdminFilter;
use App\Filters\ThrottleFilter;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'auth'          => AuthFilter::class,
        'admin'         => AdminFilter::class,
        'staff'         => StaffOrAdminFilter::class,
        'throttle'      => ThrottleFilter::class,
    ];

    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['payment/callback']],
            'invalidchars',
        ],
        'after' => [
            'secureheaders',
        ],
    ];

    public array $methods = [];
    public array $filters = [];

    public function __construct()
    {
        parent::__construct();

        // Drop CSRF + secureheaders during automated tests so PHPUnit's
        // FeatureTestTrait can hit POST endpoints without seeding tokens.
        if (ENVIRONMENT === 'testing') {
            // 'csrf' may be either a plain value or a keyed config entry
            // (['csrf' => ['except' => [...]]]); strip both shapes.
            unset($this->globals['before']['csrf']);
            $this->globals['before'] = array_values(array_diff(
                $this->globals['before'],
                ['csrf']
            ));
            $this->globals['after'] = array_values(array_diff(
                $this->globals['after'],
                ['secureheaders']
            ));
        }
    }
}
