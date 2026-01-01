# Al-Quran v2 Setup Guide

## Overview
Al-Quran v2 menggunakan API EQuran.id v2.0 dengan sistem caching lokal dan download audio otomatis dari qari Misyari Rasyid Al-Afasy.

## Fitur Utama
- ✅ Cache lokal untuk data Al-Quran (mengurangi request ke API)
- ✅ Download audio otomatis ke server lokal
- ✅ Audio berkualitas tinggi dari Misyari Rasyid Al-Afasy
- ✅ Interface responsif dan user-friendly
- ✅ Tafsir surat lengkap
- ✅ Pencarian dan filter surat

## Struktur File

```
├── pages/
│   └── alquranv2.php          # Halaman utama Al-Quran v2
├── api/
│   ├── equran_v2.php          # API backend dengan caching
│   └── download_audio.php     # Script download audio
├── scripts/
│   └── download_popular_surat.php  # Download surat populer
├── assets/
│   └── audio/
│       └── quran/             # Folder penyimpanan audio lokal
└── docs/
    └── alquran-v2-setup.md    # Dokumentasi ini
```

## Setup Awal

### 1. Pastikan Direktori Ada
Sistem akan otomatis membuat direktori yang diperlukan:
- `api/cache/equran_v2/` - Cache data API
- `assets/audio/quran/` - Audio files
- `logs/` - Log files

### 2. Permissions
Pastikan web server memiliki permission untuk menulis ke direktori:
```bash
chmod 755 api/cache/
chmod 755 assets/audio/
chmod 755 logs/
```

### 3. Download Audio (Opsional)
Untuk performa terbaik, download audio surat populer terlebih dahulu:

#### Via Command Line:
```bash
# Download surat populer
php scripts/download_popular_surat.php

# Download surat tertentu
php api/download_audio.php surat 1

# Download semua surat (1-114)
php api/download_audio.php all

# Cek statistik download
php api/download_audio.php stats
```

#### Via Web Interface:
```
http://yoursite.com/api/download_audio.php?action=stats
http://yoursite.com/api/download_audio.php?action=download_surat&surat_id=1
```

## Cara Kerja Sistem

### 1. Cache Data
- Data surat dan ayat di-cache selama 7 hari
- Cache disimpan dalam format JSON di `api/cache/equran_v2/`
- Jika cache expired, sistem otomatis fetch dari API EQuran.id

### 2. Audio Download
- Audio didownload secara background saat surat pertama kali diakses
- File audio disimpan dengan format: `surat_{id}_ayat_{id}.mp3`
- Sistem otomatis menggunakan file lokal jika tersedia
- Fallback ke CDN jika file lokal tidak ada

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

#### Download Status
```
GET /api/equran_v2.php?action=download_status
```

## Monitoring

### 1. Log Files
- `logs/equran_v2_activity.log` - Activity log
- `logs/equran_v2_error.log` - Error log
- `logs/audio_download.log` - Download log

### 2. Cache Status
Cek status cache dan download:
```php
// Cek file cache
ls -la api/cache/equran_v2/

// Cek audio files
ls -la assets/audio/quran/ | wc -l

// Cek ukuran total
du -sh assets/audio/quran/
```

### 3. Performance Tips
- Download surat populer terlebih dahulu untuk user experience terbaik
- Monitor disk space untuk audio files (~500MB untuk semua surat)
- Set up cron job untuk download otomatis:

```bash
# Crontab entry untuk download harian
0 2 * * * /usr/bin/php /path/to/scripts/download_popular_surat.php
```

## Troubleshooting

### 1. Audio Tidak Bisa Diputar
- Cek apakah file audio ada di `assets/audio/quran/`
- Cek permission file audio
- Cek log error di `logs/equran_v2_error.log`

### 2. Data Tidak Muncul
- Cek koneksi internet untuk API call
- Cek cache di `api/cache/equran_v2/`
- Cek log activity di `logs/equran_v2_activity.log`

### 3. Download Gagal
- Cek disk space
- Cek permission direktori
- Cek koneksi ke CDN audio
- Jalankan manual: `php api/download_audio.php stats`

## Maintenance

### 1. Clear Cache
```bash
rm -rf api/cache/equran_v2/*
```

### 2. Re-download Audio
```bash
rm -rf assets/audio/quran/*
php scripts/download_popular_surat.php
```

### 3. Update System
- Backup audio files sebelum update
- Update code
- Test functionality
- Restore audio files jika diperlukan

## Security Notes

1. **API Rate Limiting**: Sistem menggunakan cache untuk mengurangi API calls
2. **File Validation**: Audio files divalidasi sebelum disimpan
3. **Error Handling**: Graceful fallback ke CDN jika file lokal tidak ada
4. **Logging**: Semua aktivitas dicatat untuk monitoring

## Support

Jika mengalami masalah:
1. Cek log files di direktori `logs/`
2. Jalankan `php api/download_audio.php stats` untuk diagnostik
3. Test API endpoint secara manual
4. Cek permission direktori dan file

---

**Catatan**: Sistem ini dirancang untuk mengurangi beban server dan memberikan pengalaman user yang lebih baik dengan audio lokal yang loading lebih cepat.