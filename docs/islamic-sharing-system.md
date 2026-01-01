# Islamic Content Sharing System

Sistem sharing komprehensif untuk semua konten Islamic di Masjid Al-Muhajirin Information System.

## Fitur Utama

### 1. Sharing Spesifik per Konten
- **Al-Quran**: Surah, Ayat, Juz, rentang ayat
- **Hadits**: Hadits Arbain, Bulughul Maram, Hadits Perawi
- **Doa**: 108 Doa pilihan dengan kategori
- **Asmaul Husna**: 99 nama Allah dengan arti

### 🚧 Fitur Masa Depan (Belum Diimplementasi)
- **Tafsir**: Tafsir per ayat (method sudah disiapkan, menunggu halaman tafsir.php)
- **Dzikir**: Dzikir harian dan khusus (method sudah disiapkan, menunggu halaman dzikir.php)

### 2. Platform Sharing
- WhatsApp (dengan format khusus)
- Telegram (dengan markdown)
- Facebook (dengan quote)
- Twitter (dengan hashtag)
- LinkedIn (professional format)
- Copy Link (URL langsung)
- Copy Text (teks lengkap dengan sumber)

### 3. Format Sharing yang Disesuaikan

#### WhatsApp Format
```
*Judul Konten*

Teks Arab (jika ada)
Transliterasi (jika ada)

Artinya: "Terjemahan"

🔗 Baca selengkapnya: [URL]

📱 Masjid Al-Muhajirin
```

#### Telegram Format
```
*Judul Konten*

Teks Arab (jika ada)
Transliterasi (jika ada)

Artinya: "Terjemahan"

🔗 [Baca selengkapnya](URL)

📱 Masjid Al-Muhajirin
```

## Implementasi

### 1. Include Sistem Sharing
```php
require_once __DIR__ . '/../includes/islamic_sharing_system.php';
$sharingSystem = new IslamicSharingSystem();
```

### 2. Generate Data Sharing

#### Untuk Surah
```php
$sharingData = $sharingSystem->generateSurahSharing($surahNumber, $surahData);
```

#### Untuk Ayat
```php
$sharingData = $sharingSystem->generateAyahSharing($surahNumber, $ayahNumber, $ayahData, $surahData);
```

#### Untuk Hadits
```php
$sharingData = $sharingSystem->generateHaditsSharing($collection, $number, $haditsData, $slug);
```

#### Untuk Doa
```php
$sharingData = $sharingSystem->generateDoaSharing($doaId, $doaData);
```

#### Untuk Asmaul Husna
```php
$sharingData = $sharingSystem->generateAsmaulHusnaSharing($asmaId, $asmaData);
```

#### Untuk Tafsir (Belum Tersedia - Menunggu tafsir.php)
```php
// $sharingData = $sharingSystem->generateTafsirSharing($surahNumber, $ayahNumber, $tafsirData, $surahData);
```

#### Untuk Dzikir (Belum Tersedia - Menunggu dzikir.php)
```php
// $sharingData = $sharingSystem->generateDzikirSharing($dzikirId, $dzikirData);
```

### 3. Tampilkan Tombol Sharing

#### Modal Sharing Lengkap
```php
<button onclick="openSharingModal('<?php echo addslashes(json_encode($sharingData)); ?>', 'ayat')" 
        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
    <i class="fas fa-share-alt mr-2"></i>Bagikan
</button>
```

#### Quick Share Buttons
```php
<button onclick="shareToWhatsApp('<?php echo addslashes($sharingData['whatsapp_text']); ?>')" 
        class="flex items-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg">
    <i class="fab fa-whatsapp mr-2"></i>WhatsApp
</button>
```

### 4. Include JavaScript
```html
<script src="../assets/js/islamic-sharing.js"></script>
```

## Struktur Data Sharing

Setiap konten menghasilkan objek sharing dengan struktur:

```javascript
{
    url: "URL spesifik ke konten",
    title: "Judul konten",
    description: "Deskripsi lengkap",
    hashtags: "#hashtag #relevant",
    whatsapp_text: "Format khusus WhatsApp",
    telegram_text: "Format khusus Telegram",
    facebook_url: "URL sharing Facebook",
    twitter_url: "URL sharing Twitter", 
    linkedin_url: "URL sharing LinkedIn",
    copy_text: "Teks lengkap untuk copy"
}
```

## URL Spesifik

### Al-Quran
- Surah: `/pages/alquran.php?surah=1`
- Ayat: `/pages/alquran.php?surah=1&ayat=1`
- Juz: `/pages/alquran.php?juz=1`
- Rentang: `/pages/alquran.php?surah=1&ayat=1-7`

### Hadits
- Arbain: `/pages/hadits.php?collection=arbain&nomor=1`
- Bulughul Maram: `/pages/hadits.php?collection=bulughul_maram&nomor=1`
- Perawi: `/pages/hadits.php?collection=perawi&slug=bukhari&nomor=1`

### Doa
- Doa spesifik: `/pages/doa.php?id=1`
- Kategori: `/pages/doa.php?category=harian`

### Asmaul Husna
- Nama spesifik: `/pages/asmaul-husna.php?id=1`

## Fitur Tambahan

### 1. Native Web Share API
Sistem otomatis menggunakan Web Share API jika tersedia di browser:
```javascript
if (navigator.share) {
    await navigator.share({
        title: sharingData.title,
        text: sharingData.description,
        url: sharingData.url
    });
}
```

### 2. Keyboard Shortcuts
- `Escape`: Tutup modal sharing
- `Ctrl+Shift+S`: Buka modal sharing (jika ada data)

### 3. Copy to Clipboard
Mendukung modern Clipboard API dengan fallback untuk browser lama:
```javascript
if (navigator.clipboard && window.isSecureContext) {
    await navigator.clipboard.writeText(text);
} else {
    // Fallback method
}
```

### 4. Responsive Design
Modal sharing responsive dan mobile-friendly dengan:
- Touch-friendly buttons
- Proper spacing untuk mobile
- Icon yang jelas dan mudah dikenali

## Customization

### 1. Base URL
```php
$sharingSystem = new IslamicSharingSystem('https://custom-domain.com', 'Custom Site Name');
```

### 2. Custom Hashtags
Setiap jenis konten memiliki hashtag yang sesuai:
- Al-Quran: `#AlQuran #Surah #Islam`
- Hadits: `#Hadits #Sunnah #Islam`
- Doa: `#Doa #Islam`
- Asmaul Husna: `#AsmaulHusna #NamaAllah #Islam`

### 3. Custom Templates
Template sharing dapat disesuaikan melalui method private di class:
- `formatWhatsAppText()`
- `formatTelegramText()`
- `formatCopyText()`

## Contoh Implementasi Lengkap

Lihat file berikut untuk contoh implementasi:
- `pages/doa.php` - Sharing untuk doa
- `pages/hadits.php` - Sharing untuk hadits
- `pages/asmaul-husna.php` - Sharing untuk Asmaul Husna
- `pages/alquran-sharing-example.php` - Contoh lengkap untuk Al-Quran

## Browser Support

- Modern browsers: Full support dengan Clipboard API dan Web Share API
- Older browsers: Fallback methods untuk copy dan sharing
- Mobile browsers: Native sharing jika tersedia

## Security

- Semua input di-escape dengan `addslashes()` dan `htmlspecialchars()`
- URL encoding untuk parameter sharing
- XSS protection pada semua output

## Performance

- Lazy loading untuk modal sharing
- Minimal JavaScript footprint
- Efficient DOM manipulation
- Cached sharing data untuk menghindari regenerasi

Sistem ini memberikan pengalaman sharing yang komprehensif dan user-friendly untuk semua konten Islamic di website.