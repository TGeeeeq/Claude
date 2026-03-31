const CACHE_NAME = 'nech-me-rust-v2.2'; // Zvýšena verze pro aktualizaci

const CORE_FILES = [
    '/',
    'index.html',
    '404.html',
    'styles.css',
    'script.js',
    'manifest.json'
];

const HTML_PAGES = [
    'udalosti.html',
    'kontakt.html',
    'novinky.html',
    'jak-se-zapojit.html',
    'virtualni-adopce.html',
    'zvireci-obyvatele.html',
    'prispet-kryptem.html',
    'o-nas.html',
    'gdpr.html',
    'vop.html'
];

const ASSETS = [
    // Logos and core images
    'assets/logo.png',
    'assets/logo-circle.png',
    'assets/about-hero.webp',
    'assets/about-image.webp',
    'assets/adoption-hero.webp',
    'assets/animals-hero.webp',
    'assets/contact-hero.webp',
    'assets/hero-image.webp',
    'assets/virtual-adoption.webp',
    'assets/visit-image.webp',
    'assets/vision-image.webp',
    'assets/404.webp',
    'assets/icon-192.png',
    'assets/icon-512.png',
    'assets/click-and-feed.png',
    'assets/nakrmnas.png',

    // Animals & Others
    'assets/amalka.webp', 'assets/amalka1.webp', 'assets/amalka2.webp',
    'assets/anaya.webp', 'assets/anaya1.webp', 'assets/anaya3.webp', 'assets/anayan.webp',
    'assets/atila.webp', 'assets/atila1.webp', 'assets/atila2.webp', 'assets/atila3.webp',
    'assets/avala.webp', 'assets/avala1.webp', 'assets/avala2.webp', 'assets/avala3.webp', 'assets/avala4.webp', 'assets/avala5.webp',
    'assets/david.webp', 'assets/denis.webp', 'assets/denis1.webp', 'assets/denis2.webp', 'assets/denis3.webp',
    'assets/eda.webp', 'assets/eda1.webp', 'assets/eduard1.webp',
    'assets/emil.webp', 'assets/emil1.webp',
    'assets/flicek.webp', 'assets/flicek1.webp', 'assets/flicek2.webp', 'assets/flicek3.webp',
    'assets/hanicka.webp', 'assets/hanicka1.webp',
    'assets/holoubci.webp', 'assets/holoubci1.webp', 'assets/holoubci2.webp', 'assets/holoubci3.webp', 'assets/holoubci4.webp',
    'assets/husy1.webp', 'assets/husy2.webp', 'assets/husy3.webp', 'assets/husy4.webp', 'assets/husy5.webp',
    'assets/kachny1.webp', 'assets/kachny2.webp', 'assets/kachny3.webp', 'assets/kachny4.webp', 'assets/kachny5.webp',
    'assets/karel.webp', 'assets/karel1.webp', 'assets/karel2.webp', 'assets/karel3.webp',
    'assets/katerina.webp', 'assets/kesy.webp', 'assets/kesy1.webp', 'assets/kesy2.webp', 'assets/kesy3.webp',
    'assets/konci.webp', 'assets/konci1.webp', 'assets/konci2.webp', 'assets/konci3.webp',
    'assets/kralici.webp', 'assets/kralici1.webp', 'assets/kruta.webp',
    'assets/kulich.webp', 'assets/kulich1.webp', 'assets/kulich2.webp', 'assets/kulich3.webp',
    'assets/kveta.webp', 'assets/kveta1.webp', 'assets/kveta2.webp', 'assets/kveta3.webp',
    'assets/list.webp', 'assets/lotka.webp', 'assets/lotka1.webp', 'assets/lotka2.webp', 'assets/lotka3.webp',
    'assets/loukada1.webp', 'assets/lucinka.webp', 'assets/lucinka1.webp', 'assets/lucinka2.webp', 'assets/lucinka3.webp', 'assets/lucinka4.webp',
    'assets/maria.webp', 'assets/masa.webp', 'assets/masa1.webp', 'assets/masa2.webp', 'assets/masa3.webp',
    'assets/mazlendo.webp', 'assets/patricie.webp', 'assets/patricie1.webp', 'assets/patricie2.webp',
    'assets/pipinky.webp', 'assets/pipinky1.webp', 'assets/pipinky2.webp', 'assets/pipinky3.webp', 'assets/pipinky4.webp', 'assets/pipinky5.webp',
    'assets/pogo.webp', 'assets/pogo1.webp', 'assets/pogo2.webp', 'assets/pogo3.webp', 'assets/pogo4.webp',
    'assets/princezna.webp', 'assets/princezna1.webp', 'assets/princezna2.webp', 'assets/princezna3.webp', 'assets/princezna4.webp',
    'assets/princi.webp', 'assets/prochazka.webp', 'assets/prochazka1.webp', 'assets/prochazka2.webp', 'assets/prochazka3.webp', 'assets/prochazkan.webp',
    'assets/rikous.webp', 'assets/riky.webp', 'assets/riky1.webp',
    'assets/roman.webp', 'assets/roman1.webp', 'assets/roman2.webp', 'assets/roman3.webp', 'assets/roman4.webp',
    'assets/safir.webp', 'assets/safir1.webp', 'assets/safir2.webp', 'assets/safir4.webp', 'assets/safir5.webp',
    'assets/sklep.webp', 'assets/tomas.webp', 'assets/toulky1.webp', 'assets/valentyn.jpg',
    'assets/yakul.webp', 'assets/yakul1.webp', 'assets/yakul2.webp', 'assets/yakul3.webp', 'assets/yakul5.webp', 'assets/yakuln.webp',
    'assets/zorkan.webp', 'assets/zorkaodchod.webp',

    // Products
    'images/products/batoh.webp', 'images/products/gomasio.webp', 'images/products/kolodejove.webp',
    'images/products/kolodejove2.webp', 'images/products/kolodejove3.webp', 'images/products/kralovna.webp',
    'images/products/lisak.webp', 'images/products/naramek.webp', 'images/products/nausnice.webp',
    'images/products/plakat1.webp', 'images/products/plakat2.webp', 'images/products/plakat3.webp',
    'images/products/plakat4.webp', 'images/products/plakat5.webp', 'images/products/plakat6.webp',
    'images/products/plakat7.webp', 'images/products/plakat8.webp', 'images/products/privesek1.webp',
    'images/products/privesek2.webp', 'images/products/privesek3.webp', 'images/products/privesek4.webp',
    'images/products/privesek5.webp', 'images/products/privesek6.webp', 'images/products/privesek7.webp',
    'images/products/privesek8.webp', 'images/products/privesek9.webp', 'images/products/rebrickova-mast.webp',
    'images/products/taska-karel.webp', 'images/products/vesak-briza.webp', 'images/products/vesak-svetly.webp',
    'images/products/vesak-tmavy.webp',

    // Crypto
    'assets/bitcoin-logo.png', 'assets/ethereum-logo.png', 'assets/cardano-logo.png',
    'assets/bnb-logo.png', 'assets/pi-logo.png',
    'assets/btc-qr.png', 'assets/eth-qr.png', 'assets/ada-qr.png',
    'assets/bnb-qr.png', 'assets/pi-qr.png',

    // Video
    'assets/video-poster.webp',
    'assets/animals.mp4'
];

const EXTERNAL_RESOURCES = [
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

const urlsToCache = [
    ...CORE_FILES,
    ...HTML_PAGES,
    ...ASSETS,
    ...EXTERNAL_RESOURCES
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching all files...');
                return Promise.all(
                    urlsToCache.map(url => {
                        return cache.add(url).catch(err => {
                            console.warn(`[SW] Failed to cache ${url}:`, err);
                        });
                    })
                );
            })
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match('offline.html'))
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request).then(networkResponse => {
                 // Cache external resources on the fly
                if (EXTERNAL_RESOURCES.includes(event.request.url)) {
                     const cache = caches.open(CACHE_NAME);
                     cache.put(event.request, networkResponse.clone());
                }
                return networkResponse;
            });
        })
    );
});