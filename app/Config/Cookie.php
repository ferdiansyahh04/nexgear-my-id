<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use DateTimeInterface;

class Cookie extends BaseConfig
{
    /**
     * Cookie name prefix to avoid collisions.
     */
    public string $prefix = '';

    /**
     * Default expires timestamp. `0` makes it a session cookie.
     *
     * @var DateTimeInterface|int|string
     */
    public $expires = 0;

    /**
     * Cookie path. Typically a forward slash.
     */
    public string $path = '/';

    /**
     * Cookie domain. Set to `.your-domain.com` for site-wide cookies.
     */
    public string $domain = '';

    /**
     * Cookie will only be set if a secure HTTPS connection exists.
     * Auto-flipped to true in production via the constructor below.
     */
    public bool $secure = false;

    /**
     * Cookie will only be accessible via HTTP(S) (no JavaScript).
     */
    public bool $httponly = true;

    /**
     * SameSite attribute. Allowed: `None`, `Lax`, `Strict`, `''`.
     * `Lax` is the modern default for cross-site nav safety.
     *
     * @var ''|'Lax'|'None'|'Strict'
     */
    public string $samesite = 'Lax';

    /**
     * Skip rawurlencode() on the cookie name+value when set.
     */
    public bool $raw = false;

    public function __construct()
    {
        parent::__construct();

        // In production, every cookie must travel over HTTPS only.
        // App config already flips forceGlobalSecureRequests=true there;
        // this aligns the cookie policy with that decision.
        if (ENVIRONMENT === 'production') {
            $this->secure = true;
        }
    }
}
