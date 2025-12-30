# Requirements Document

## Introduction

Sistem Autentikasi & Hak Akses adalah fondasi dari Sistem Informasi Terpadu Masjid Jami Al-Muhajirin yang memungkinkan pengelolaan akses berbasis peran untuk berbagai pengguna sistem. Sistem ini akan mengatur akses ke modul website masjid publik dan sistem internal bimbel dengan tiga tingkat hak akses: Admin Masjid, Admin Bimbel, dan Viewer.

## Requirements

### Requirement 1

**User Story:** Sebagai Admin Bimbel, saya ingin dapat login ke sistem dengan kredensial yang aman, sehingga saya dapat mengakses semua fitur manajemen bimbel.

#### Acceptance Criteria

1. WHEN pengguna mengakses halaman login THEN sistem SHALL menampilkan form login dengan field username dan password
2. WHEN pengguna memasukkan kredensial yang valid THEN sistem SHALL membuat session dan mengarahkan ke dashboard sesuai role
3. WHEN pengguna memasukkan kredensial yang tidak valid THEN sistem SHALL menampilkan pesan error dan tetap di halaman login
4. WHEN pengguna sudah login dan mengakses halaman login THEN sistem SHALL mengarahkan ke dashboard sesuai role

### Requirement 2

**User Story:** Sebagai Admin Masjid, saya ingin dapat mengakses laporan dan monitoring sistem, sehingga saya dapat memantau keseluruhan operasional masjid dan bimbel.

#### Acceptance Criteria

1. WHEN Admin Masjid login THEN sistem SHALL memberikan akses ke dashboard monitoring dan laporan
2. WHEN Admin Masjid mengakses modul bimbel THEN sistem SHALL memberikan akses read-only untuk monitoring
3. WHEN Admin Masjid mencoba mengakses fitur CRUD bimbel THEN sistem SHALL menampilkan pesan akses terbatas

### Requirement 3

**User Story:** Sebagai Viewer, saya ingin dapat melihat laporan ringkas, sehingga saya dapat memantau informasi dasar tanpa dapat mengubah data.

#### Acceptance Criteria

1. WHEN Viewer login THEN sistem SHALL memberikan akses hanya ke laporan ringkas
2. WHEN Viewer mencoba mengakses fitur CRUD THEN sistem SHALL menampilkan pesan akses ditolak
3. WHEN Viewer mengakses dashboard THEN sistem SHALL menampilkan ringkasan data tanpa opsi edit

### Requirement 4

**User Story:** Sebagai pengguna yang sudah login, saya ingin dapat logout dengan aman, sehingga session saya berakhir dan data terlindungi.

#### Acceptance Criteria

1. WHEN pengguna mengklik tombol logout THEN sistem SHALL menghapus session dan mengarahkan ke halaman login
2. WHEN session expired THEN sistem SHALL otomatis logout dan mengarahkan ke halaman login
3. WHEN pengguna logout THEN sistem SHALL mencegah akses ke halaman admin tanpa login ulang

### Requirement 5

**User Story:** Sebagai administrator sistem, saya ingin sistem memiliki keamanan session yang baik, sehingga data pengguna terlindungi dari akses tidak sah.

#### Acceptance Criteria

1. WHEN pengguna login THEN sistem SHALL menggunakan session PHP yang aman dengan regenerasi session ID
2. WHEN terjadi aktivitas mencurigakan THEN sistem SHALL mencatat log keamanan
3. WHEN session tidak aktif selama 30 menit THEN sistem SHALL otomatis logout pengguna
4. IF pengguna belum login THEN sistem SHALL mengarahkan ke halaman login untuk akses halaman admin

### Requirement 6

**User Story:** Sebagai pengguna sistem, saya ingin dapat mengakses website masjid publik tanpa login, sehingga informasi masjid dapat diakses oleh jamaah umum.

#### Acceptance Criteria

1. WHEN pengunjung mengakses halaman publik THEN sistem SHALL menampilkan konten tanpa memerlukan autentikasi
2. WHEN pengunjung mengakses halaman admin THEN sistem SHALL mengarahkan ke halaman login
3. WHEN pengunjung mengakses API publik THEN sistem SHALL memberikan data yang diizinkan tanpa autentikasi

### Requirement 7

**User Story:** Sebagai administrator, saya ingin dapat mengelola pengguna sistem, sehingga dapat menambah, mengubah, atau menonaktifkan akses pengguna.

#### Acceptance Criteria

1. WHEN Admin Masjid mengelola pengguna THEN sistem SHALL menyediakan CRUD untuk data pengguna
2. WHEN membuat pengguna baru THEN sistem SHALL mengenkripsi password dengan algoritma yang aman
3. WHEN mengubah password THEN sistem SHALL memvalidasi password lama dan mengenkripsi password baru
4. WHEN menonaktifkan pengguna THEN sistem SHALL mencegah login tanpa menghapus data historis