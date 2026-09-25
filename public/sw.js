/*
 * Service worker SIAKAD UNMARIS.
 *
 * Prinsip: allowlist. Hanya asset statis yang tidak pernah berubah per-hash
 * (icon, manifest, build Vite, asset publik Filament) yang boleh masuk cache.
 * Semua halaman — login, dashboard, PDF, pembayaran, Livewire, API — selalu
 * diteruskan ke network TANPA pernah disimpan, supaya tidak ada data
 * pengguna yang tertinggal di cache perangkat.
 */

const CACHE_VERSION = 'siakad-v1';
const CACHE_NAME = `static-${CACHE_VERSION}`;

/* Prefiks yang aman dimcache: seluruh berkasnya sudah versioned atau statis. */
const CACHEABLE_PREFIXES = [
    '/icons/',
    '/images/',
    '/favicons/',
    '/css/',
    '/js/',
    '/fonts/',
    '/build/',
    '/manifest.webmanifest',
    '/favicon.ico',
];

/* Jangan pernah cache ini, walaupun polanya cocok di atas. */
const NEVER_CACHE = [
    '/livewire/',
    '/api/',
    '/storage/',
    '/admin',
    '/dosen',
    '/mahasiswa',
    '/password/',
    '/pdf/',
    '/pembayaran/',
];

self.addEventListener('install', (event) => {
    // Tidak ada precache HTML. Asset masuk cache hanya saat diminta,
    // sehingga perilaku setelah deploy selalu mengikuti server.
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key.startsWith('static-') && key !== CACHE_NAME)
                        .map((key) => caches.delete(key)),
                ),
            ),
        ]),
    );
});

function isCacheablePath(pathname) {
    if (NEVER_CACHE.some((prefix) => pathname.startsWith(prefix))) {
        return false;
    }

    return CACHEABLE_PREFIXES.some((prefix) => pathname.startsWith(prefix));
}

function isEligibleResponse(request, response) {
    // Hanya same-origin; respons cross-origin (font Google, dsb.) dilarang
    // karena bersifat opaque dan tidak bisa diperiksa.
    if (new URL(request.url).origin !== self.location.origin) {
        return false;
    }

    if (request.method !== 'GET') {
        return false;
    }

    // Halaman navigasi (semua HTML, termasuk 3 panel) tidak pernah masuk cache.
    if (request.mode === 'navigate' || request.destination === 'document') {
        return false;
    }

    if (!response || !response.ok || response.status !== 200) {
        return false;
    }

    if (response.type !== 'basic') {
        return false;
    }

    if (response.redirected) {
        return false;
    }

    // Respons yang membawa Set-Cookie berarti terikat sesi pengguna.
    if (response.headers.has('Set-Cookie')) {
        return false;
    }

    return isCacheablePath(new URL(request.url).pathname);
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    if (request.mode === 'navigate') {
        // Network-only: jangan pernah melayani halaman dari cache, agar
        // aplikasi tidak pernah menampilkan data basi atau milik sesi lain.
        return;
    }

    if (!isCacheablePath(new URL(request.url).pathname)) {
        return;
    }

    event.respondWith(
        caches.open(CACHE_NAME).then(async (cache) => {
            const cached = await cache.match(request);

            if (cached) {
                return cached;
            }

            const response = await fetch(request);

            if (isEligibleResponse(request, response)) {
                // Clone sebelum respons dikonsumsi oleh browser.
                cache.put(request, response.clone());
            }

            return response;
        }),
    );
});
