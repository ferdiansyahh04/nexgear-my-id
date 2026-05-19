/**
 * NexGear PWA service worker.
 *
 * Strategy:
 *  - Precache a small app shell on install
 *  - Network-first for navigation requests; falls back to cached shell when offline
 *  - Stale-while-revalidate for static assets (CSS/JS/fonts/icons)
 *  - Bypass for everything that mutates state (POST, /admin, /cart, /checkout, /coupon, /wishlist)
 */
const VERSION = 'nexgear-v1';
const SHELL_CACHE = `${VERSION}-shell`;
const RUNTIME_CACHE = `${VERSION}-runtime`;

const SHELL_URLS = [
    '/',
    '/manifest.webmanifest',
    '/assets/css/app.css',
    '/assets/js/app.js',
    '/assets/icons/icon-192.svg',
    '/assets/icons/icon-512.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE)
            .then((cache) => cache.addAll(SHELL_URLS).catch(() => null))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(
            keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))
        );
        await self.clients.claim();
    })());
});

function shouldBypass(request, url) {
    if (request.method !== 'GET') return true;
    const path = url.pathname;
    if (path.startsWith('/admin')) return true;
    if (path.startsWith('/account')) return true;
    if (path.startsWith('/cart')) return true;
    if (path.startsWith('/checkout')) return true;
    if (path.startsWith('/coupon')) return true;
    if (path.startsWith('/wishlist')) return true;
    if (path.startsWith('/login')) return true;
    if (path.startsWith('/register')) return true;
    if (path.startsWith('/logout')) return true;
    if (path.startsWith('/products/search')) return true;
    if (path.endsWith('/quick-view')) return true;
    if (path.endsWith('/stock')) return true;
    return false;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (url.origin !== self.location.origin) return;
    if (shouldBypass(request, url)) return;

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
        return;
    }

    const dest = request.destination;
    if (['style', 'script', 'image', 'font'].includes(dest)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }
});

async function networkFirst(request) {
    try {
        const fresh = await fetch(request);
        const cache = await caches.open(RUNTIME_CACHE);
        cache.put(request, fresh.clone()).catch(() => null);
        return fresh;
    } catch (err) {
        const cached = await caches.match(request) || await caches.match('/');
        if (cached) return cached;
        return new Response('Offline. Reconnect to browse NexGear.', {
            status: 503,
            statusText: 'Offline',
            headers: { 'Content-Type': 'text/plain' },
        });
    }
}

async function staleWhileRevalidate(request) {
    const cache = await caches.open(RUNTIME_CACHE);
    const cached = await cache.match(request);
    const fetchPromise = fetch(request).then((response) => {
        if (response && response.status === 200) {
            cache.put(request, response.clone()).catch(() => null);
        }
        return response;
    }).catch(() => cached);
    return cached || fetchPromise;
}
