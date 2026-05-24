<?php

/**
 * Asset URL helper — produces cache-busted URLs for static files in public/.
 *
 * Strategy:
 *   - Compute filemtime() of the file on disk
 *   - Append ?v=<hex> as a query string
 *   - Browsers + Cloudflare treat each version as a distinct URL, so a
 *     fresh deploy never serves a stale cached copy
 *
 * The mtime is cached in a static array so multiple calls within the same
 * request don't hit the filesystem repeatedly.
 *
 * Usage in views:
 *   <link href="<?= asset_url('assets/css/app.css') ?>" rel="stylesheet">
 *   <script src="<?= asset_url('assets/js/app.js') ?>"></script>
 */

if (! function_exists('asset_url')) {
    /**
     * Generate a base_url() to a public asset, with a cache-busting query
     * string derived from the file's last-modified time.
     *
     * If the file doesn't exist at FCPATH (because we're in a packed
     * deployment or path is wrong), we fall back to the plain base_url so
     * the page still renders.
     */
    function asset_url(string $relativePath): string
    {
        static $versions = [];

        $relativePath = ltrim($relativePath, '/');

        if (! isset($versions[$relativePath])) {
            $absolute = FCPATH . $relativePath;
            $versions[$relativePath] = is_file($absolute)
                ? dechex((int) filemtime($absolute))
                : null;
        }

        $url = base_url($relativePath);
        $version = $versions[$relativePath];

        if ($version === null) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . 'v=' . $version;
    }
}
