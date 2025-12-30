# PWA (Progressive Web App) Setup

## Fitur PWA yang Sudah Diimplementasi

### ✅ Service Worker
- Caching offline untuk halaman utama
- Background sync untuk update jadwal sholat
- Push notifications untuk pengingat sholat

### ✅ Web App Manifest
- Installable sebagai aplikasi native
- Custom icons dan splash screen
- Shortcuts untuk akses cepat

### ✅ Push Notifications
- Notifikasi pengingat waktu sholat
- Notifikasi berita dan pengumuman penting
- Permission request yang user-friendly

## Setup Push Notifications

### 1. Generate VAPID Keys
Untuk menggunakan push notifications, Anda perlu generate VAPID keys:

```bash
# Install web-push globally
npm install -g web-push

# Generate VAPID keys
web-push generate-vapid-keys
```

### 2. Update VAPID Key
Ganti `YOUR_VAPID_PUBLIC_KEY_HERE` di `index.php` dengan public key yang dihasilkan.

### 3. Install Web Push Library (PHP)
```bash
composer require minishlink/web-push
```

### 4. Update send_notification.php
Implementasikan library web-push untuk mengirim notifikasi real.

## Icon Requirements

Buat icon dengan ukuran berikut dan simpan di `assets/images/`:

- favicon.ico (16x16, 32x32, 48x48)
- favicon-16x16.png
- favicon-32x32.png
- icon-72x72.png
- icon-96x96.png
- icon-128x128.png
- icon-144x144.png
- icon-152x152.png
- icon-180x180.png (Apple)
- icon-192x192.png
- icon-384x384.png
- icon-512x512.png
- mstile-150x150.png (Windows)

## Testing PWA

### 1. HTTPS Requirement
PWA memerlukan HTTPS untuk production. Untuk testing lokal:
- Chrome: `chrome://flags/#unsafely-treat-insecure-origin-as-secure`
- Tambahkan `http://localhost` ke daftar

### 2. Chrome DevTools
- Buka DevTools > Application > Service Workers
- Test push notifications di Application > Notifications
- Audit PWA dengan Lighthouse

### 3. Install Testing
- Desktop: Chrome akan menampilkan install prompt
- Mobile: "Add to Home Screen" di browser menu

## Fitur Tambahan yang Bisa Ditambahkan

### Background Sync
- Sync data saat online kembali
- Queue actions saat offline

### Web Share API
- Share artikel dan jadwal sholat
- Native sharing di mobile

### Geolocation
- Auto-detect lokasi untuk jadwal sholat
- Arah kiblat berdasarkan lokasi

### Camera API
- Scan QR code untuk donasi
- Upload foto kegiatan

## Troubleshooting

### Service Worker Tidak Update
```javascript
// Force update service worker
navigator.serviceWorker.getRegistrations().then(function(registrations) {
    for(let registration of registrations) {
        registration.unregister();
    }
});
```

### Push Notification Tidak Muncul
1. Check browser permissions
2. Verify VAPID keys
3. Check console untuk error
4. Test dengan simple payload

### PWA Tidak Installable
1. Pastikan manifest.json valid
2. Service worker harus registered
3. HTTPS required (kecuali localhost)
4. Minimal 2 icons (192px dan 512px)

## Production Checklist

- [ ] Generate dan setup VAPID keys
- [ ] Buat semua icon yang diperlukan
- [ ] Setup HTTPS
- [ ] Test di berbagai browser
- [ ] Implement real push notification library
- [ ] Setup cron job untuk auto-notification sholat
- [ ] Add error tracking (Sentry, etc.)
- [ ] Performance optimization
- [ ] Offline fallback pages