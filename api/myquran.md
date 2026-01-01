
GET
nomor
https://api.myquran.com/v2/hadits/arbain/:nomor
Untuk mendapatkan hadits arbain berdasarkan nomornya.

Format
/hadits/arbain/:nomor
Contoh
/hadits/arbain/1
PATH VARIABLES
nomor
1

nomor hadits arbain, bertipe integer range 1-42

Example Request
nomor
curl
curl --location 'https://api.myquran.com/v2/hadits/arbain/1'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
Bulughul Maram

Bulughul Maram atau Bulugh al-Maram min Adillat al-Ahkam, disusun oleh Al-Hafizh Ibnu Hajar Al-Asqalani (773 H - 852 H).

Kitab ini merupakan kitab hadis tematik yang memuat hadis-hadis yang dijadikan sumber pengambilan hukum fikih (istinbath) oleh para ahli fikih. Kitab ini termasuk kitab fikih yang menerima pengakuan global dan juga banyak diterjemahkan di seluruh dunia.

GET
nomor
https://api.myquran.com/v2/hadits/bm/:nomor
Untuk mendapatkan hadits Bulughul Maram berdasarkan nomornya.

Format
/hadits/bm/:nomor
Keterangan
nomor diisi dengan angka (int), minimal 1 dan maksimal 1597
Contoh:

/hadits/bm/1
PATH VARIABLES
nomor
1597

bertipe integer, range 1-1597

Example Request
nomor
curl
curl --location 'https://api.myquran.com/v2/hadits/bm/1597'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
acak
https://api.myquran.com/v2/hadits/bm/acak
menampilkan hadits Bulughul Maram secara acak.

berguna untuk quotes harian atau reminder atau hal lainnya.

Format

/hadits/bm/acak

/hadits/bm/random


Example Request
acak
curl
curl --location 'https://api.myquran.com/v2/hadits/bm/acak'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
hadits
https://api.myquran.com/v2/hadits//:slug/:nomor
Menampilkan hadits berdasarkan perawinya.

Format
/hadis/:slug/:nomor
/hadits/:slug/:nomor
Contoh
/hadits/bukhari/1
/hadits/ahmad/3
dan lainnya...
PATH VARIABLES
slug
bukhari

slug perawi

nomor
1

nomor hadits, bertipe integer

Example Request
hadits
curl
curl --location 'https://api.myquran.com/v2/hadits//bukhari/1'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
acak
https://api.myquran.com/v2/hadits/perawi/acak
Mendapatkan hadits secara acak

Format
/hadits/perawi/acak
/hadits/perawi/random

Example Request
acak
curl
curl --location 'https://api.myquran.com/v2/hadits/perawi/acak'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
Doa
Kumpulan doa-doa


Harian
Koleksi doa harian dari berbagai sumber

GET
acak
https://api.myquran.com/v2/doa/acak
Mendapatkan doa harian secara acak.

Format
/doa/acak
/doa/random
Sample Hasil:
View More
json
{
  "status": true,
  "request": {
    "path": "/doa/acak",
    "id": 48
  },
  "data": {
    "arab": "اللَّهُمَّ إِنِّى أَعُوذُ بِكَ مِنَ الْخُبُثِ وَالْخَبَائِث",
    "indo": "Ya Allah, sesungguhnya aku berlindung kepada-Mu dari setan-setan lelaki dan setan-setan perempuan.",
    "judul": "Doa Masuk WC",
    "source": "harian"
  }
}

Example Request
acak
curl
curl --location 'https://api.myquran.com/v2/doa/acak'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
id
https://api.myquran.com/v2/doa//:id
Mendapatkan doa berdasarkan nomor urutan.

Format:
/doa/:id
Keterangan
nomor adalah berupa angka atau bilangan (int), dengan minimal 1 dan maksimal 108
Contoh
/doa/1
/doa/81
PATH VARIABLES
id
nomor id bertipe integer dengan range 1-108

Example Request
id
curl
curl --location 'https://api.myquran.com/v2/doa//:id'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
list sumber
https://api.myquran.com/v2/doa/sumber
Untuk mendapatkan list atau daftar sumber-sumber doa.

Format

/doa/sumber
Contoh Hasil

View More
json
{
    "status": true,
    "request": {
        "path": "/doa/sumber"
    },
    "data": [
        "quran",
        "hadits",
        "pilihan",
        "harian",
        "ibadah",
        "haji",
        "lainnya"
    ]
}
Example Request
list sumber
curl
curl --location 'https://api.myquran.com/v2/doa/sumber'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body

Asmaul Husna

Allah SWT berfirman

اَللّٰهُ لَاۤ اِلٰهَ اِلَّا هُوَ ‌ؕ لَـهُ الۡاَسۡمَآءُ الۡحُسۡنٰى

“Allahu Laa Ilaaha Illaa huwa Lahul Asmaaul Husna”

“Tidak ada Tuhan Melainkan Allah. Dialah Allah yang memiliki Asmaul Husna atau nama-nama yang terbaik.” (QS. Thaha ayat 8).

GET
acak
https://api.myquran.com/v2/husna/acak
Mendapatkan data asmaul husna secara acak.

Format

/husna/acak
/husna/random
Contoh Hasil

View More
json
{
  "status": true,
  "request": {
    "path": "/husna/acak",
    "id": 37
  },
  "data": {
    "arab": "الْكَبِيْرُ",
    "id": 37,
    "indo": "Yang Maha Besar",
    "latin": "Al-Kabîru"
  }
}

Example Request
acak
curl
curl --location 'https://api.myquran.com/v2/husna/acak'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
id
https://api.myquran.com/v2/husna//:nomor
Mendapatkan data asmaul husna berdasarkan nomor urutan.

Format:
/husna/:nomor
Keterangan
nomor adalah berupa angka atau bilangan (int), dengan minimal 1 dan maksimal 99
Contoh Request
/husna/1
/husna/81
Contoh Hasil
View More
json
{
    "status": true,
    "request": {
        "path": "/husna/1",
        "id": "1"
    },
    "info": {
        "min": 1,
        "max": 99
    },
    "data": {
        "arab": "الرَّحْمـٰنُ",
        "id": 1,
        "indo": "Yang Maha Pengasih",
        "latin": "Ar-Rahmânu"
    }
}
PATH VARIABLES
nomor
1

nomor index asmaul husna bertipe integer range 1-99

Example Request
id
curl
curl --location 'https://api.myquran.com/v2/husna//1'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
semua
https://api.myquran.com/v2/husna/semua
Mendapatkan list asmaul husna secara lengkap

Format:
/husna/semua
/husna/all
Example Request
semua
curl
curl --location 'https://api.myquran.com/v2/husna/semua'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
