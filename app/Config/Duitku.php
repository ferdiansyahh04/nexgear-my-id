<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Duitku Pop payment configuration.
 *
 * Values come from the environment (.env) — NEVER commit real keys. Grab the
 * Merchant Code + API Key (Merchant Key) from the Duitku merchant portal
 * (Project page). Use Sandbox while testing; flip `production` to true and
 * swap to the production project keys when going live.
 *
 * .env example:
 *   duitku.merchantCode = DSxxxx
 *   duitku.apiKey       = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *   duitku.production   = false
 */
class Duitku extends BaseConfig
{
    public string $merchantCode = '';
    public string $apiKey       = '';

    /**
     * Sandbox (false) vs Production (true). Controls both the API host and
     * the duitku.js loader URL.
     */
    public bool $production = false;

    /**
     * Transaction expiry in minutes (Duitku accepts e.g. 5, 10, 60).
     */
    public int $expiryPeriod = 60;

    /**
     * Whether the integration is configured. Checkout falls back to the
     * legacy "place order without payment" flow when this is false, so the
     * site never breaks if keys are missing.
     */
    public function isEnabled(): bool
    {
        return $this->merchantCode !== '' && $this->apiKey !== '';
    }

    /**
     * Create-invoice API base host.
     */
    public function apiBase(): string
    {
        return $this->production
            ? 'https://api-prod.duitku.com'
            : 'https://api-sandbox.duitku.com';
    }

    /**
     * Front-end duitku.js (Pop) loader URL.
     */
    public function popJsUrl(): string
    {
        return $this->production
            ? 'https://app-prod.duitku.com/lib/js/duitku.js'
            : 'https://app-sandbox.duitku.com/lib/js/duitku.js';
    }
}
