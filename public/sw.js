/* People & Pixel Service Worker - Offline-first MVP
 * - Precache core shell and CSS
 * - Runtime cache for GET navigations and static assets
 * - Offline fallback for navigations
 * - Queue POSTs to /tasks/new and /tasks/move when offline or on failure; flush via Background Sync (if available) or on next startup
 */
const VERSION = 'pp-sw-v1';
const CORE_CACHE = VERSION + '-core';
const RUNTIME_CACHE = VERSION + '-rt';
const QUEUE_DB = 'pp-sw-queue';
const QUEUE_STORE = 'requests';

const CORE_URLS = [
  '/',
  '/style.css',
  '/manifest.webmanifest',
  '/offline.html',
  '/icons/app-icon.svg',
  '/icons/sprite.svg',
  // external CSS is via CDN; cannot precache cross-origin easily
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CORE_CACHE).then((cache) => cache.addAll(CORE_URLS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter(k => !k.startsWith(VERSION)).map(k => caches.delete(k)));
    await self.clients.claim();
  })());
});

// IndexedDB helpers for queue
function idb() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(QUEUE_DB, 1);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(QUEUE_STORE)) {
        db.createObjectStore(QUEUE_STORE, { keyPath: 'id', autoIncrement: true });
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}
async function queueRequest(data) {
  const db = await idb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(QUEUE_STORE, 'readwrite');
    tx.objectStore(QUEUE_STORE).add(data);
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
}
async function readAllQueued() {
  const db = await idb();
  return await new Promise((resolve, reject) => {
    const tx = db.transaction(QUEUE_STORE, 'readonly');
    const req = tx.objectStore(QUEUE_STORE).getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror = () => reject(req.error);
  });
}
async function clearQueued(ids) {
  const db = await idb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(QUEUE_STORE, 'readwrite');
    const store = tx.objectStore(QUEUE_STORE);
    ids.forEach(id => store.delete(id));
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
}

async function flushQueue() {
  const queued = await readAllQueued();
  if (!queued.length) return;
  const okIds = [];
  for (const item of queued) {
    try {
      const init = { method: item.method, headers: item.headers, body: item.body, credentials: 'same-origin' };
      const res = await fetch(item.url, init);
      if (res && res.ok) { okIds.push(item.id); }
    } catch (e) { /* still offline or failed */ }
  }
  if (okIds.length) { await clearQueued(okIds); }
}

self.addEventListener('sync', (event) => {
  if (event.tag === 'pp-task-queue') {
    event.waitUntil(flushQueue());
  }
});

function isTaskMutation(url, method) {
  if (method !== 'POST') return false;
  try {
    const u = new URL(url, self.location.origin);
    return u.pathname === '/tasks/new' || u.pathname === '/tasks/move';
  } catch { return false; }
}

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Only handle same-origin requests
  if (url.origin !== self.location.origin) return;

  // Navigation requests: offline-first with fallback
  if (req.mode === 'navigate') {
    event.respondWith((async () => {
      try {
        const net = await fetch(req);
        const cache = await caches.open(RUNTIME_CACHE);
        cache.put(req, net.clone());
        return net;
      } catch (e) {
        const cached = await caches.match(req);
        if (cached) return cached;
        return caches.match('/offline.html');
      }
    })());
    return;
  }

  // Queue task mutations when offline or on failure
  if (isTaskMutation(url.pathname, req.method)) {
    event.respondWith((async () => {
      try {
        const res = await fetch(req.clone());
        return res;
      } catch (e) {
        // Clone request body for queue (assumes form-urlencoded or JSON)
        let body = null;
        if (req.method === 'POST') {
          try { body = await req.clone().text(); } catch {}
        }
        const headers = {};
        req.headers.forEach((v, k) => { headers[k] = v; });
        await queueRequest({ url: url.pathname + url.search, method: req.method, headers, body, ts: Date.now() });
        if ('sync' in self.registration) {
          try { await self.registration.sync.register('pp-task-queue'); } catch {}
        } else {
          // Best-effort immediate flush on next focus
          setTimeout(flushQueue, 0);
        }
        return new Response(JSON.stringify({ ok: false, queued: true }), { status: 202, headers: { 'Content-Type': 'application/json' } });
      }
    })());
    return;
  }

  // Static assets and GET requests: cache strategies
  if (req.method === 'GET') {
    if (url.pathname.endsWith('.css') || url.pathname.endsWith('.js') || url.pathname.endsWith('.png') || url.pathname.endsWith('.jpg') || url.pathname.endsWith('.svg') || url.pathname.startsWith('/website/')) {
      // Cache First for static
      event.respondWith((async () => {
        const cached = await caches.match(req);
        if (cached) return cached;
        const res = await fetch(req);
        const cache = await caches.open(RUNTIME_CACHE);
        cache.put(req, res.clone());
        return res;
      })());
      return;
    }
    // Stale-While-Revalidate for other GETs
    event.respondWith((async () => {
      const cache = await caches.open(RUNTIME_CACHE);
      const cached = await cache.match(req);
      const fetchPromise = fetch(req).then((networkResponse) => {
        cache.put(req, networkResponse.clone());
        return networkResponse;
      }).catch(() => cached || caches.match('/offline.html'));
      return cached || fetchPromise;
    })());
  }
});

// Attempt to flush queue periodically when regaining connectivity
self.addEventListener('online', () => { flushQueue(); });
