# Al-Quran v2 Setup Guide

## Overview
Al-Quran v2 menggunakan API EQuran.id v2.0 dengan sistem caching lokal dan streaming audio langsung dari CDN.

## Fitur Utama
- ✅ Cache lokal untuk data Al-Quran (mengurangi request ke API)
- ✅ Streaming audio langsung dari CDN berkualitas tinggi
- ✅ Audio dari qari Misyari Rasyid Al-Afasy
- ✅ Interface responsif dan user-friendly
- ✅ Tafsir surat lengkap
- ✅ Pencarian dan filter surat
- ✅ Fitur canggih: Last Read, Bookmark, Favorit, Highlight, Resume
- ✅ SweetAlert untuk notifikasi yang elegan

## Struktur File

```
├── pages/
│   └── alquranv2.php          # Halaman utama Al-Quran v2
├── api/
│   └── equran_v2.php          # API backend dengan caching
├── docs/
│   ├── alquran-v2-setup.md    # Dokumentasi setup
│   └── alquran-v2-advanced-features.md  # Dokumentasi fitur canggih
└── test_advanced_features.html # Testing interface
```

## Setup Awal

### 1. Pastikan Direktori Ada
Sistem akan otomatis membuat direktori yang diperlukan:
- `api/cache/equran_v2/` - Cache data API
- `logs/` - Log files

### 2. Permissions
Pastikan web server memiliki permission untuk menulis ke direktori:
```bash
chmod 755 api/cache/
chmod 755 logs/
```

## Cara Kerja Sistem

### 1. Cache Data
- Data surat dan ayat di-cache selama 7 hari
- Cache disimpan dalam format JSON di `api/cache/equran_v2/`
- Jika cache expired, sistem otomatis fetch dari API EQuran.id

### 2. Audio Streaming
- Audio di-stream langsung dari CDN EQuran.id
- Tidak ada penyimpanan audio lokal
- Kualitas tinggi dari qari Misyari Rasyid Al-Afasy
- Fallback otomatis jika ada masalah koneksi

### 3. API Endpoints

#### Get Surat List
```
GET /api/equran_v2.php?action=surat_list
```

#### Get Surat Detail
```
GET /api/equran_v2.php?action=surat_detail&surat_id=1
```

#### Get Tafsir
```
GET /api/equran_v2.php?action=tafsir&surat_id=1
```

#### Download Status (Now Streaming Status)
```
GET /api/equran_v2.php?action=download_status
```

## Fitur Canggih

### 1. LocalStorage Features
- **Last Read**: Penanda ayat terakhir dibaca
- **Bookmarks**: Simpan ayat dengan catatan
- **Favorites**: Surat favorit
- **Highlights**: 5 warna highlight dengan catatan
- **Reading Progress**: Progress bacaan per surat
- **Reading Stats**: Statistik bacaan lengkap

### 2. Audio Sync
- Highlight otomatis saat audio berjalan
- Auto-scroll mengikuti ayat yang diputar
- Visual feedback yang smooth

### 3. SweetAlert Integration
- Popup yang elegan dan user-friendly
- Input dialog untuk catatan
- Konfirmasi yang interaktif
- Notifikasi yang menarik

## Monitoring

### 1. Log Files
- `logs/equran_v2_activity.log` - Activity log
- `logs/equran_v2_error.log` - Error log

### 2. Cache Status
Cek status cache:
```php
// Cek file cache
ls -la api/cache/equran_v2/
```

### 3. Performance Tips
- Cache data surat untuk performa optimal
- Streaming audio mengurangi beban server
- LocalStorage untuk fitur user tanpa database

## Troubleshooting

### 1. Audio Tidak Bisa Diputar
- Cek koneksi internet
- Cek console browser untuk error
- Pastikan CDN EQuran.id dapat diakses

### 2. Data Tidak Muncul
- Cek koneksi internet untuk API call
- Cek cache di `api/cache/equran_v2/`
- Cek log activity di `logs/equran_v2_activity.log`

### 3. Fitur LocalStorage Tidak Berfungsi
- Pastikan browser mendukung localStorage
- Cek apakah localStorage tidak penuh
- Test dengan browser lain

## Maintenance

### 1. Clear Cache
```bash
rm -rf api/cache/equran_v2/*
```

### 2. Clear LocalStorage
- Gunakan test interface di `test_advanced_features.html`
- Atau manual via browser developer tools

### 3. Update System
- Backup cache jika diperlukan
- Update code
- Test functionality

## Security Notes

1. **API Rate Limiting**: Sistem menggunakan cache untuk mengurangi API calls
2. **Data Validation**: Input validation untuk semua parameter
3. **Error Handling**: Graceful fallback untuk semua error
4. **Logging**: Semua aktivitas dicatat untuk monitoring
5. **LocalStorage**: Data user tersimpan lokal dan private

## Browser Compatibility

### Supported Features
- localStorage (IE8+)
- CSS3 animations (IE10+)
- ES6 features (modern browsers)
- Audio streaming (all modern browsers)
- SweetAlert2 (modern browsers)

### Requirements
- Modern browser dengan JavaScript enabled
- Koneksi internet untuk API dan audio streaming
- LocalStorage support untuk fitur canggih

---

**Catatan**: Sistem ini menggunakan streaming audio dari CDN dan localStorage untuk fitur user, memberikan pengalaman yang optimal tanpa beban server yang berat.