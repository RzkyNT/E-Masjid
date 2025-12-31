
GET
sepanjang
https://api.myquran.com/v2/quran/ayat/:surat/:ayat/:panjang
Format
/quran/ayat/:surat/:ayat/:panjang
Contoh:
/quran/ayat/2/1/5
Untuk menampilkan surat ke 2 (alBaqarah) mulai dari ayat 1 sepanjang 5 ayat (ayat 1 - 5).

PATH VARIABLES
surat
2

nomor surat, bertipe integer range 1-114

ayat
1

nomor ayat dimulai, bertipe integer maksimal jangkauan saat ini adalah 30

panjang
5

Example Request
sepanjang
curl
curl --location 'https://api.myquran.com/v2/quran/ayat/2/1/5'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
sampai dengan
https://api.myquran.com/v2/quran/ayat/:surat/:range
Format
/quran/ayat/:surat/:noAyatStart-:noAyatEnd
Keterangan:
noAyatStart (int) nomor ayat dimulai
noAyatEnd (int) nomor ayat berakhir
Ingat, ada tanda minus - di sini

Contoh
/quran/ayat/2/3-5

menampilkan surat ke 2, dari ayat 3 sampai dengan ayat 5

PATH VARIABLES
surat
2

nomor surat, bertipe integer dengan range 1-114

range
3-5

nomor ayat jangkauan, dengan tanda - (minus) misal akan ditampilkan ayat 3 sampai dengan 5

Example Request
sampai dengan
curl
curl --location 'https://api.myquran.com/v2/quran/ayat/2/3-5'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
page
https://api.myquran.com/v2/quran/ayat/page/:nomor
Menampilkan ayat quran berdasarkan halamannya

Format
/quran/ayat/page/:nomor
Contoh
/quran/ayat/page/1
untuk menampilkan ayat alQuran pada halaman 1.

PATH VARIABLES
nomor
1

nomor halaman, bertipe integer range 1-604

Example Request
page
curl
curl --location 'https://api.myquran.com/v2/quran/ayat/page/1'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
juz
https://api.myquran.com/v2/quran/ayat/juz/:juz
Menampilkan ayat quran berdasarkan juz

Format
/quran/ayat/juz/:nomor
Keterangan
nomor (int) adalah nomor halaman, minimal 1 dan maksimum adalah 30
Contoh
/quran/ayat/juz/1
untuk menampilkan seluruh ayat alQuran yang terdapat pada juz 1.

PATH VARIABLES
juz
1

nomor juz, bertipe integer range 1-30

Example Request
juz
curl
curl --location 'https://api.myquran.com/v2/quran/ayat/juz/1'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
Juz
Menampilkan informasi juz.

Jika ingin menampilkan ayatnya, pergunakan metode /quran/ayat/juz

GET
nomor
https://api.myquran.com/v2/quran/juz/:nomor
Untuk mendapatkan informasi per juz.

Format
/quran/juz/:nomor
Contoh:
/quran/juz/1
Akan menampilkan informasi juz 1.

PARAMS
PATH VARIABLES
nomor
1

nomor juz, bertipe integer range 1-30

Example Request
nomor
curl
curl --location 'https://api.myquran.com/v2/quran/juz/1'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
id
https://api.myquran.com/v2/quran/tema/:id
Untuk mendapatkan informasi per tema.

Format
/quran/tema/:id
Contoh:
/quran/tema/1
Akan menampilkan informasi tema 1.

PARAMS
PATH VARIABLES
id
1

id tema, bertipe integer range 1-1.121

Example Request
id
curl
curl --location 'https://api.myquran.com/v2/quran/tema/1'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
semua
https://api.myquran.com/v2/quran/tema/semua
Menampilkan keseluruhan informasi tema yang ada

Format
/quran/tema/semua
/quran/tema/all
