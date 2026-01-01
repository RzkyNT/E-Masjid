untuk quran bagaimana jika kita menggunakan api ini? "Langsung ke konten utama

EQuran.id

Beranda

Doa

Game

AI Chat

Lainnya

Kembali ke API Hub

API Al-Quran v2.0

Direkomendasikan

API terbaru dengan fitur lengkap, audio berkualitas tinggi, dan format response yang lebih terstruktur

Tentang API v2.0

API terbaru dengan fitur enhanced, dukungan audio dari 6 qari terbaik, dan format response yang mengikuti best practice REST API

3

Endpoint Utama

6

Qari Audio

24j

Cache CDN

Endpoint Surat Enhanced

Mengakses data surat dengan fitur audio dan response terstruktur

Daftar Surat:

GET /api/v2/surat

Detail Surat:

GET /api/v2/surat/{nomor}

Tafsir Surat:

GET /api/v2/tafsir/{nomor}

Fitur Audio

Audio berkualitas tinggi dari qari terpilih

6 Qari Terbaik:

• Abdullah Al-Juhany

• Abdul Muhsin Al-Qasim

• Abdurrahman As-Sudais

• Ibrahim Al-Dossari

• Misyari Rasyid Al-Afasy

• Yasser Al-Dosari

Format Audio:

• Audio per ayat (individual)

• Audio surat lengkap

• Format MP3 berkualitas tinggi

• CDN global untuk loading cepat

Fitur Utama

Keunggulan API v2.0 EQuran.id

Enhanced Features:

Status wrapper response (code, message, data)

Audio per ayat dari 6 qari terbaik

CDN global untuk performa optimal

Error handling yang lebih baik

Konten Tersedia:

114 surat Al-Quran lengkap

6,236 ayat dengan audio

Tafsir lengkap setiap surat

Metadata lengkap (tempat turun, arti, dll)

Testing Interaktif

Test API v2.0 secara langsung dengan interface seperti Postman

Test semua endpoint secara real-time. Klik "Run Test" untuk melihat response langsung dari API.

GET

/surat

Open

Mendapatkan daftar semua surat dengan audio lengkap

Uji APIParameterContoh

URL Request

Jalankan Uji

Perintah cURLSalin

curl "https://equran.id/api/v2/surat"

GET

/surat/{id}

Open

Mendapatkan detail surat beserta ayat dan audio per ayat

Uji APIParameterContoh

URL Request

Parameter

id

Wajib

number

Jalankan Uji

Perintah cURLSalin

curl "https://equran.id/api/v2/surat/{id}"

GET

/tafsir/{id}

Open

Mendapatkan tafsir lengkap dengan format enhanced

Uji APIParameterContoh

URL Request

Parameter

id

Wajib

number

Jalankan Uji

Perintah cURLSalin

curl "https://equran.id/api/v2/tafsir/{id}"

💡 Tips Testing

• Response API v2.0 menggunakan status wrapper (code, message, data)

• Setiap ayat dilengkapi dengan audio dari 6 qari terbaik

• Audio tersedia dalam format MP3 berkualitas tinggi

• Gunakan CDN global untuk loading audio yang optimal

Demo Aplikasi

Lihat API v2.0 bekerja dalam aplikasi nyata

Kunjungi halaman utama untuk melihat bagaimana API v2.0 digunakan dalam aplikasi Al-Quran digital dengan fitur audio.

Buka Aplikasi Al-Quran

Kembali ke API HubLihat API v1.0Bandingkan API

EQuran.id

Al-Quran Digital Indonesia

Platform Al-Quran digital terlengkap dengan terjemahan bahasa Indonesia, audio berkualitas tinggi dari 6 qari terbaik, tafsir lengkap, doa harian, dan game edukatif.

114

Surat

6.236

Ayat

6

Qari

Fitur Utama

Baca Al-QuranDoa HarianGame EdukatifAPI DeveloperStatus Page

Legal

Terms of ServicePrivacy Policy

Partner

Islamic NetworkVerse By Verse Quran

Made by Muslim from Indonesia with

© 2026 EQuran.id. All rights reserved.

API Developer v2.0 | EQuran.id - Al Quran Digital Bahasa Indonesia | EQuran.id"

