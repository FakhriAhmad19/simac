/* SIMAC service worker — enables install (PWA) + light offline handling.
   Kept conservative: never caches dynamic/authenticated HTML, only static assets. */
const CACHE = 'simac-static-v1';
const ASSETS = [
    '/favicon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/manifest.webmanifest',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((c) => c.addAll(ASSETS)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    // Static assets: cache-first (fast, offline-capable).
    if (/\.(?:png|svg|ico|css|js|webmanifest|woff2?)$/.test(url.pathname)) {
        event.respondWith(
            caches.match(req).then((hit) => hit || fetch(req).then((res) => {
                const copy = res.clone();
                caches.open(CACHE).then((c) => c.put(req, copy));
                return res;
            }).catch(() => hit))
        );
        return;
    }

    // Everything else (dynamic/authenticated pages): network-first, no caching.
    event.respondWith(
        fetch(req).catch(() =>
            new Response(
                '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' +
                '<title>Offline · SIMAC</title>' +
                '<div style="font-family:system-ui;text-align:center;padding:3rem 1.5rem;color:#334155">' +
                '<div style="font-size:3rem">❄️</div><h1 style="color:#2563eb">Sedang Offline</h1>' +
                '<p>Anda tidak terhubung ke internet. Periksa koneksi lalu coba lagi.</p></div>',
                { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            )
        )
    );
});
