# Sistem Informasi Masjid & Bimbel Al-Muhajirin

## Gambaran Umum

Sistem Informasi Terpadu Masjid Jami Al-Muhajirin yang mencakup:

1. **Website Resmi Masjid** (informasi & publik)
2. **Sistem Informasi Bimbel Al-Muhajirin** (internal & manajerial)

## Fitur Utama

### Website Publik
- Informasi masjid dan kegiatan
- Jadwal sholat otomatis
- Berita dan pengumuman
- Galeri foto kegiatan
- Sistem donasi transparan
- Progressive Web App (PWA)

### Admin Panel
- Manajemen konten dinamis
- Upload dan manajemen file
- Sistem backup/restore
- Laporan keuangan
- Manajemen pengguna

## Teknologi

- **Backend**: PHP 8.0+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript, Tailwind CSS
- **Editor**: Quill.js untuk rich text
- **PWA**: Service Worker untuk offline support
- **API**: Prayer times dari MyQuran API

## Setup & Instalasi

1. **Persiapan Database**
   ```bash
   # Import database
   mysql -u root -p masjid_bimbel < database/masjid_bimbel.sql
   mysql -u root -p masjid_bimbel < database/donation_system.sql
   ```

2. **Konfigurasi**
   - Edit `config/config.php` untuk database dan pengaturan
   - Jalankan `initialize_website.php` untuk setup awal
   - Jalankan `seed_content.php` untuk data sample

3. **Akses Admin**
   - URL: `/admin/login.php`
   - Default: admin@masjid.com / admin123

## Struktur Direktori

```
├── admin/              # Panel administrasi
├── api/               # API endpoints
├── assets/            # CSS, JS, images
├── config/            # Konfigurasi sistem
├── database/          # SQL files
├── includes/          # PHP includes & helpers
├── pages/             # Halaman publik
├── partials/          # Template parts
└── logs/              # System logs
```

## Dokumentasi Teknis

Untuk dokumentasi detail, lihat file-file berikut:
- `SETUP_README.md` - Panduan instalasi lengkap
- `PWA_README.md` - Dokumentasi Progressive Web App
- `BERITA_ENHANCEMENT_README.md` - Fitur berita dan editor
- `CONTENT_HELPER_README.md` - Sistem rendering konten Quill.js
- `SETTINGS_SYSTEM_README.md` - Sistem pengaturan dinamis
- `IMAGE_PATH_FIX_SUMMARY.md` - Perbaikan sistem upload gambar

## Troubleshooting

Lihat dokumentasi berikut untuk masalah umum:
- `QUILL_TROUBLESHOOTING.md` - Masalah editor Quill.js
- `ERROR_FIX_SUMMARY.md` - Perbaikan error umum
- `UPLOAD_SECURITY_FIX.md` - Masalah upload gambar dan keamanan
- `IMAGE_PATH_FIX_SUMMARY.md` - Masalah path gambar

## Kontribusi

Sistem ini dikembangkan untuk Masjid Jami Al-Muhajirin dengan fokus pada kemudahan penggunaan dan maintenance.

## Lisensi

Sistem ini dikembangkan khusus untuk Masjid Jami Al-Muhajirin.