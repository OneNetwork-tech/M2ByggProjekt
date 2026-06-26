/* M2 Leverantörsportal — service worker (app-shell cache only, no offline data sync) */
const CACHE_NAME = 'm2-leverantor-v1';
const SHELL_ASSETS = [
  '/leverantor/assets/portal.css',
  '/leverantor/assets/icons/icon-192.png',
  '/leverantor/assets/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  // Shell assets: cache-first
  if (SHELL_ASSETS.some((asset) => req.url.endsWith(asset))) {
    event.respondWith(
      caches.match(req).then((cached) => cached || fetch(req))
    );
    return;
  }

  // Everything else (PHP pages with live data): network-first, no offline fallback for data
  event.respondWith(
    fetch(req).catch(() => caches.match(req))
  );
});
