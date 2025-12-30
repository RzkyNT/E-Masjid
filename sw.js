// Service Worker for Masjid Al-Muhajirin Website
// Provides offline functionality and caching

const CACHE_NAME = 'masjid-almuhajirin-v4';
const urlsToCache = [
    './',
    './index.php',
    './pages/profil.php',
    './pages/jadwal_sholat.php',
    './pages/berita.php',
    './pages/donasi.php',
    './pages/kontak.php',
    './assets/css/style.css'
];

// Don't try to cache external resources in service worker
// They will be cached by browser naturally when loaded via <script> and <link> tags

// Install event - cache local resources only
self.addEventListener('install', function(event) {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Opened cache');
                // Only cache local resources
                return cache.addAll(urlsToCache);
            })
            .then(() => {
                console.log('Service Worker installation complete');
                // Skip waiting to activate immediately
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Cache installation failed:', error);
            })
    );
});

// Fetch event - serve from cache when offline with better error handling
self.addEventListener('fetch', function(event) {
    // Skip chrome-extension requests, non-GET requests, and external resources
    if (event.request.url.startsWith('chrome-extension://') || 
        event.request.method !== 'GET' ||
        event.request.url.startsWith('https://cdn.') ||
        event.request.url.startsWith('https://cdnjs.')) {
        return;
    }
    
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Return cached version if available
                if (response) {
                    console.log('Serving from cache:', event.request.url);
                    return response;
                }
                
                // Fetch from network
                console.log('Fetching from network:', event.request.url);
                return fetch(event.request)
                    .then(function(response) {
                        // Don't cache non-successful responses or external resources
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        
                        // Only cache same-origin requests
                        if (event.request.url.startsWith(self.location.origin)) {
                            // Clone the response for caching
                            const responseToCache = response.clone();
                            
                            caches.open(CACHE_NAME)
                                .then(function(cache) {
                                    cache.put(event.request, responseToCache);
                                });
                        }
                        
                        return response;
                    })
                    .catch(function(error) {
                        console.warn('Fetch failed for:', event.request.url, error);
                        
                        // Return a basic offline page for navigation requests
                        if (event.request.mode === 'navigate') {
                            return new Response(
                                '<!DOCTYPE html><html><head><title>Offline</title></head><body><h1>Offline</h1><p>Halaman tidak tersedia saat offline.</p><button onclick="window.location.reload()">Coba Lagi</button></body></html>',
                                { headers: { 'Content-Type': 'text/html' } }
                            );
                        }
                        
                        // For other requests, just let them fail
                        throw error;
                    });
            })
    );
});

// Activate event - clean up old caches and take control immediately
self.addEventListener('activate', function(event) {
    console.log('Service Worker activating...');
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            caches.keys().then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if (cacheName !== CACHE_NAME) {
                            console.log('Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            // Take control of all clients immediately
            self.clients.claim()
        ]).then(() => {
            console.log('Service Worker activated and ready');
        })
    );
});

// Background sync for prayer time updates
self.addEventListener('sync', function(event) {
    if (event.tag === 'prayer-times-sync') {
        event.waitUntil(updatePrayerTimes());
    }
});

// Push notifications for prayer times
self.addEventListener('push', function(event) {
    const options = {
        body: event.data ? event.data.text() : 'Waktu sholat telah tiba',
        icon: './assets/images/icon-192x192.png',
        badge: './assets/images/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Lihat Jadwal',
                icon: './assets/images/checkmark.png'
            },
            {
                action: 'close',
                title: 'Tutup',
                icon: './assets/images/xmark.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('Masjid Al-Muhajirin', options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('./pages/jadwal_sholat.php')
        );
    } else {
        // Default action - open main page
        event.waitUntil(
            clients.openWindow('./')
        );
    }
});

// Update prayer times function
async function updatePrayerTimes() {
    try {
        const response = await fetch('./api/prayer_times.php?action=today');
        const data = await response.json();
        
        if (data.success) {
            // Store updated prayer times in localStorage
            localStorage.setItem('prayer_times', JSON.stringify(data.data));
            console.log('Prayer times updated');
        }
    } catch (error) {
        console.error('Failed to update prayer times:', error);
    }
}