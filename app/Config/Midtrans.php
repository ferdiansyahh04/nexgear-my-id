<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Midtrans Snap configuration.
 *
 * Values come from the environment (.env) — NEVER commit real keys. Copy the
 * keys from your Midtrans dashboard (Settings → Access Keys). Use the Sandbox
 * keys while testing; flip `production` to true and swap to production keys
 * when going live.
 *
 * .env example:
 *   midtrans.serverKey = SB-Mid-server-xxxxxxxxxxxxxxxx
 *   midtrans.clientKey = SB-Mid-client-xxxxxxxxxxxxxxxx
 *   midtrans.production = false
 */
class Midtrans extends BaseConfig
{
    public string $serverKey = '';
    public string $clientKey = '';

    /**
     * Sandbox (false) vs Production (true). Controls both the Snap API host
     * and the snap.js script URL.
     */
    public bool $production = false;

    /**
     * Whether the integration is configured. Checkout falls back to the
     * legacy "place order without payment" flow when this is false, so the
     * site never breaks if keys are missing.
     */
    public function isEnabled(): bool
    {
        return $this->serverKey !== '' && $this->clientKey !== '';
    }

    /**
     * Snap API base host (create-transaction endpoint lives here).
     */
    public function apiBase(): string
    {
        return $this->production
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Front-end snap.js loader URL.
     */
    public function snapJsUrl(): string
    {
        return $this->production
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
}
