# Requirements Document

## Introduction

Sistem Manajemen Bimbel Al-Muhajirin adalah modul internal untuk mengelola operasional bimbingan belajar yang berada di bawah naungan Masjid Jami Al-Muhajirin. Sistem ini mencakup manajemen siswa, mentor, absensi, keuangan, dan pelaporan untuk jenjang SD, SMP, dan SMA dengan fokus pada transparansi keuangan dan kemudahan pengelolaan.

## Requirements

### Requirement 1

**User Story:** Sebagai Admin Bimbel, saya ingin dapat mengelola data siswa dengan lengkap, sehingga saya dapat memantau dan mengorganisir siswa berdasarkan jenjang dan kelas.

#### Acceptance Criteria

1. WHEN Admin Bimbel mengelola siswa THEN sistem SHALL menyediakan CRUD untuk data siswa lengkap
2. WHEN Admin Bimbel menambah siswa THEN sistem SHALL memvalidasi data dan mengatur jenjang serta kelas
3. WHEN Admin Bimbel melihat daftar siswa THEN sistem SHALL menampilkan filter berdasarkan jenjang, kelas, dan status aktif
4. WHEN Admin Bimbel mengubah status siswa THEN sistem SHALL memperbarui status tanpa menghapus data historis

### Requirement 2

**User Story:** Sebagai Admin Bimbel, saya ingin dapat mengelola data mentor dan pengajar, sehingga saya dapat mengatur jadwal mengajar dan perhitungan honor.

#### Acceptance Criteria

1. WHEN Admin Bimbel mengelola mentor THEN sistem SHALL menyediakan CRUD untuk data mentor lengkap
2. WHEN Admin Bimbel menambah mentor THEN sistem SHALL mengatur jenjang yang diajar dan tarif honor per kehadiran
3. WHEN Admin Bimbel melihat daftar mentor THEN sistem SHALL menampilkan informasi jenjang ajar dan status aktif
4. WHEN Admin Bimbel mengubah tarif mentor THEN sistem SHALL menyimpan riwayat perubahan tarif

### Requirement 3

**User Story:** Sebagai Admin Bimbel, saya ingin dapat mencatat absensi siswa secara efisien, sehingga saya dapat memantau kehadiran dan membuat laporan kehadiran.

#### Acceptance Criteria

1. WHEN Admin Bimbel mencatat absensi siswa THEN sistem SHALL menyediakan interface per tanggal dan kelas
2. WHEN Admin Bimbel melihat absensi THEN sistem SHALL menampilkan daftar siswa dengan status kehadiran
3. WHEN Admin Bimbel membuat laporan kehadiran THEN sistem SHALL menghitung persentase kehadiran per siswa
4. WHEN Admin Bimbel melihat rekap absensi THEN sistem SHALL menampilkan statistik kehadiran bulanan

### Requirement 4

**User Story:** Sebagai Admin Bimbel, saya ingin dapat mencatat absensi mentor, sehingga saya dapat menghitung honor yang harus dibayarkan berdasarkan kehadiran.

#### Acceptance Criteria

1. WHEN Admin Bimbel mencatat absensi mentor THEN sistem SHALL menyediakan interface per tanggal dan jenjang
2. WHEN Admin Bimbel melihat absensi mentor THEN sistem SHALL menampilkan daftar mentor dengan status kehadiran
3. WHEN Admin Bimbel menghitung honor THEN sistem SHALL otomatis menghitung berdasarkan kehadiran dan tarif
4. WHEN Admin Bimbel melihat rekap absensi mentor THEN sistem SHALL menampilkan total kehadiran dan honor bulanan

### Requirement 5

**User Story:** Sebagai Admin Bimbel, saya ingin dapat mengelola pembayaran SPP siswa, sehingga saya dapat memantau status pembayaran dan membuat laporan keuangan.

#### Acceptance Criteria

1. WHEN Admin Bimbel mengelola SPP THEN sistem SHALL menyediakan pencatatan pembayaran per siswa per bulan
2. WHEN Admin Bimbel mencatat pembayaran THEN sistem SHALL memperbarui status pembayaran dan saldo
3. WHEN Admin Bimbel melihat status SPP THEN sistem SHALL menampilkan siswa dengan tunggakan dan yang sudah lunas
4. WHEN Admin Bimbel membuat laporan SPP THEN sistem SHALL menampilkan ringkasan pembayaran bulanan

### Requirement 6

**User Story:** Sebagai Admin Bimbel, saya ingin dapat mengelola keuangan bimbel secara komprehensif, sehingga saya dapat memantau pemasukan, pengeluaran, dan saldo bimbel.

#### Acceptance Criteria

1. WHEN Admin Bimbel mengelola keuangan THEN sistem SHALL menyediakan pencatatan pemasukan dan pengeluaran
2. WHEN Admin Bimbel mencatat transaksi THEN sistem SHALL mengkategorikan transaksi (SPP, pendaftaran, operasional, honor)
3. WHEN Admin Bimbel melihat laporan keuangan THEN sistem SHALL menampilkan ringkasan bulanan otomatis
4. WHEN Admin Bimbel memantau saldo THEN sistem SHALL menampilkan saldo real-time berdasarkan transaksi

### Requirement 7

**User Story:** Sebagai Admin Bimbel, saya ingin sistem dapat membuat rekap bulanan otomatis, sehingga saya dapat dengan mudah membuat laporan rutin tanpa perhitungan manual.

#### Acceptance Criteria

1. WHEN sistem membuat rekap bulanan THEN sistem SHALL otomatis menghitung total pemasukan dari SPP dan pendaftaran
2. WHEN sistem membuat rekap bulanan THEN sistem SHALL otomatis menghitung total pengeluaran operasional dan honor mentor
3. WHEN sistem membuat rekap bulanan THEN sistem SHALL menampilkan saldo awal, mutasi, dan saldo akhir
4. WHEN Admin Bimbel melihat rekap bulanan THEN sistem SHALL menyediakan export ke format PDF atau Excel

### Requirement 8

**User Story:** Sebagai Admin Masjid, saya ingin dapat memantau laporan keuangan bimbel, sehingga saya dapat mengawasi operasional bimbel tanpa dapat mengubah data.

#### Acceptance Criteria

1. WHEN Admin Masjid mengakses laporan bimbel THEN sistem SHALL memberikan akses read-only ke laporan keuangan
2. WHEN Admin Masjid melihat dashboard bimbel THEN sistem SHALL menampilkan ringkasan keuangan dan statistik
3. WHEN Admin Masjid mengakses detail transaksi THEN sistem SHALL menampilkan riwayat transaksi tanpa opsi edit
4. WHEN Admin Masjid melihat laporan THEN sistem SHALL menyediakan filter berdasarkan periode dan kategori

### Requirement 9

**User Story:** Sebagai Viewer, saya ingin dapat melihat laporan ringkas bimbel, sehingga saya dapat memantau performa bimbel tanpa akses ke data sensitif.

#### Acceptance Criteria

1. WHEN Viewer mengakses laporan bimbel THEN sistem SHALL menampilkan ringkasan statistik tanpa detail keuangan
2. WHEN Viewer melihat dashboard THEN sistem SHALL menampilkan jumlah siswa, mentor, dan tingkat kehadiran
3. WHEN Viewer mengakses laporan THEN sistem SHALL menyembunyikan informasi keuangan detail
4. WHEN Viewer melihat data THEN sistem SHALL menampilkan tren kehadiran dan performa umum

### Requirement 10

**User Story:** Sebagai pengguna sistem bimbel, saya ingin sistem terintegrasi dengan sistem autentikasi masjid, sehingga saya dapat menggunakan satu akun untuk mengakses semua fitur yang sesuai dengan hak akses saya.

#### Acceptance Criteria

1. WHEN pengguna login THEN sistem SHALL menggunakan sistem autentikasi yang sama dengan website masjid
2. WHEN pengguna mengakses modul bimbel THEN sistem SHALL memvalidasi hak akses berdasarkan role
3. WHEN Admin Bimbel login THEN sistem SHALL mengarahkan ke dashboard bimbel dengan akses penuh
4. WHEN Admin Masjid atau Viewer login THEN sistem SHALL memberikan akses sesuai dengan tingkat hak akses

### Requirement 11

**User Story:** Sebagai Admin Bimbel, saya ingin sistem dapat menangani multiple jenjang (SD, SMP, SMA) dengan baik, sehingga saya dapat mengelola semua jenjang dalam satu sistem terpadu.

#### Acceptance Criteria

1. WHEN Admin Bimbel mengelola siswa THEN sistem SHALL mendukung pengelompokan berdasarkan jenjang dan kelas
2. WHEN Admin Bimbel mengatur mentor THEN sistem SHALL memungkinkan mentor mengajar multiple jenjang
3. WHEN Admin Bimbel mencatat absensi THEN sistem SHALL memisahkan absensi berdasarkan jenjang dan kelas
4. WHEN Admin Bimbel membuat laporan THEN sistem SHALL menyediakan filter dan grouping berdasarkan jenjang

### Requirement 12

**User Story:** Sebagai Admin Bimbel, saya ingin sistem memiliki validasi data yang baik, sehingga data yang tersimpan selalu akurat dan konsisten.

#### Acceptance Criteria

1. WHEN Admin Bimbel memasukkan data siswa THEN sistem SHALL memvalidasi format data dan mencegah duplikasi
2. WHEN Admin Bimbel mencatat pembayaran THEN sistem SHALL memvalidasi jumlah dan tanggal pembayaran
3. WHEN Admin Bimbel mencatat absensi THEN sistem SHALL mencegah duplikasi absensi pada tanggal yang sama
4. WHEN Admin Bimbel memasukkan data keuangan THEN sistem SHALL memvalidasi kategori dan jumlah transaksi