# Context Proyek: Sistem Informasi Masjid & Bimbel Al-Muhajirin

## 1. Gambaran Umum

Proyek ini adalah pengembangan **Sistem Informasi Terpadu Masjid Jami Al-Muhajirin** yang mencakup:

1. **Website Resmi Masjid** (informasi & publik)
2. **Sistem Informasi Bimbel Al-Muhajirin** (internal & manajerial)

Sistem dibangun **terintegrasi dalam satu website dan satu database**, dengan pembagian modul dan hak akses.

Target pengguna:

* Jamaah umum
* Pengurus DKM
* Pengelola Bimbel
* Mentor & staf internal

---

## 2. Informasi Masjid

* **Nama**: Masjid Jami Al-Muhajirin
* **Alamat**: Q2X5+P3M, Jl. Bumi Alinda Kencana, Kaliabang Tengah, Bekasi Utara, Kota Bekasi
* **Jenis**: Masjid Jami
* **Fungsi**:

  * Tempat ibadah
  * Pusat dakwah
  * Kegiatan sosial
  * Pendidikan (Bimbel)

---

## 3. Informasi Bimbel

* **Nama**: Bimbel Al-Muhajirin
* **Naungan**: Masjid Jami Al-Muhajirin
* **Jenjang**: SD, SMP, SMA
* **Aktivitas**:

  * Pembelajaran rutin
  * Absensi siswa
  * Absensi mentor
  * Pembayaran SPP
  * Insentif mentor & staf

---

## 4. Tujuan Pengembangan Sistem

1. Digitalisasi pengelolaan masjid & bimbel
2. Transparansi keuangan
3. Sentralisasi data (tidak terpisah Excel)
4. Kemudahan pelaporan
5. Dasar pengembangan SIM jangka panjang

---

## 5. Teknologi & Batasan Teknis

* **Backend**: PHP Native (tanpa framework)
* **Database**: MySQL / MariaDB
* **Frontend**: HTML5, CSS3, Tailwind CSS (CDN)
* **Hosting Target**: InfinityFree / shared hosting
* **Keamanan**: Session-based auth

⚠️ Hindari framework berat (Laravel, React, dll)

---

## 6. Struktur Folder Proyek

```
/masjid/
│
├── index.php
├── config/
│   ├── config.php
│   └── auth.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── partials/
│   ├── header.php
│   ├── footer.php
│   └── sidebar.php
│
├── pages/
│   ├── profil.php
│   ├── jadwal_sholat.php
│   ├── berita.php
│   ├── galeri.php
│   ├── donasi.php
│   └── kontak.php
│
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── masjid/
│   └── bimbel/
│       ├── dashboard.php
│       ├── siswa.php
│       ├── mentor.php
│       ├── spp.php
│       ├── absensi_siswa.php
│       ├── absensi_mentor.php
│       ├── keuangan.php
│       └── laporan.php
│
└── database/
    └── masjid_bimbel.sql
```

---

## 7. Modul Website Masjid (Publik)

### 7.1 Beranda

* Hero section
* Highlight fasilitas
* Jadwal sholat hari ini
* Pengumuman

### 7.2 Profil Masjid

* Sejarah
* Visi & misi
* Struktur DKM

### 7.3 Jadwal Sholat

* Otomatis (API)
* Manual fallback

### 7.4 Berita & Kegiatan

* Kajian
* Pengumuman

### 7.5 Donasi & Infaq

* Rekening
* QRIS
* Laporan ringkas

### 7.6 Galeri

* Foto & video

---

## 8. Modul Sistem Bimbel (Internal)

### 8.1 Manajemen Siswa

* Data siswa
* Jenjang & kelas
* Status aktif

### 8.2 Manajemen Mentor

* Data mentor
* Jenjang ajar
* Honor per hadir

### 8.3 Absensi Siswa

* Per tanggal
* Rekap kehadiran

### 8.4 Absensi Mentor

* Per tanggal & jenjang
* Dasar perhitungan insentif

### 8.5 Keuangan Bimbel

* Pemasukan (SPP, pendaftaran)
* Pengeluaran (operasional, insentif)
* Rekap bulanan otomatis

### 8.6 Laporan

* Keuangan bulanan
* Honor mentor
* Absensi

---

## 9. Database (Garis Besar)

Tabel utama:

* users
* siswa
* mentor
* spp
* absensi_siswa
* absensi_mentor
* keuangan
* rekap_bulanan

Relasi sederhana tanpa ORM.

---

## 10. Hak Akses

* **Admin Masjid**: monitoring & laporan
* **Admin Bimbel**: CRUD penuh modul bimbel
* **Viewer**: laporan ringkas

---

## 11. Prinsip Pengembangan

* Modular & reusable
* Mudah dipahami
* Aman untuk shared hosting
* Fokus kebutuhan nyata

---

## 12. Catatan Penting untuk AI Agent

* Gunakan PHP Native
* Gunakan prepared statement
* Hindari overengineering
* Buat fitur bertahap

---

**End of context.md**
