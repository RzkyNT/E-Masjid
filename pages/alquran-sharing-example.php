<?php
/**
 * Al-Quran Page with Sharing System - Example Implementation
 * For Masjid Al-Muhajirin Information System
 * 
 * Demonstrates comprehensive sharing functionality for Surah and Ayah
 */

// Include required files
require_once __DIR__ . '/../includes/myquran_api.php';
require_once __DIR__ . '/../includes/islamic_content_renderer.php';
require_once __DIR__ . '/../includes/islamic_sharing_system.php';

// Initialize classes
$api = new MyQuranAPI();
$renderer = new IslamicContentRenderer();
$sharingSystem = new IslamicSharingSystem();

// Handle parameters
$surahNumber = isset($_GET['surah']) ? (int)$_GET['surah'] : 1;
$ayahNumber = isset($_GET['ayat']) ? (int)$_GET['ayat'] : null;

// Sample data (in real implementation, this would come from API)
$surahData = [
    'number' => $surahNumber,
    'name' => 'الفاتحة',
    'name_latin' => 'Al-Fatihah',
    'meaning' => 'Pembukaan',
    'verses_count' => 7,
    'revelation_place' => 'Makkah'
];

$ayahData = null;
if ($ayahNumber) {
    $ayahData = [
        'number' => $ayahNumber,
        'text' => 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
        'translation' => 'Dengan nama Allah Yang Maha Pengasih, Maha Penyayang'
    ];
}

$page_title = $ayahNumber ? 
    "QS. {$surahData['name_latin']}:{$ayahNumber} - Al-Quran" : 
    "Surah {$surahData['name_latin']} - Al-Quran";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/islamic-content.css">
</head>
<body class="bg-gray-50">
    <?php include '../partials/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        
        <?php if ($ayahNumber): ?>
            <!-- Ayah Detail Mode -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-900">
                        QS. <?php echo $surahData['name_latin']; ?>:<?php echo $ayahNumber; ?>
                    </h1>
                    <div class="flex items-center gap-2">
                        <?php 
                        $ayahSharingData = $sharingSystem->generateAyahSharing($surahNumber, $ayahNumber, $ayahData, $surahData);
                        ?>
                        <button onclick="openSharingModal('<?php echo addslashes(json_encode($ayahSharingData)); ?>', 'ayat')" 
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-share-alt mr-2"></i>Bagikan Ayat
                        </button>
                        <button onclick="copyToClipboard('<?php echo addslashes($ayahSharingData['copy_text']); ?>')" 
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                            <i class="fas fa-copy mr-2"></i>Salin
                        </button>
                    </div>
                </div>
                
                <!-- Ayah Content -->
                <div class="text-center mb-6">
                    <div class="text-3xl font-arabic text-gray-800 mb-4 leading-relaxed" dir="rtl">
                        <?php echo htmlspecialchars($ayahData['text']); ?>
                    </div>
                    <div class="text-lg text-gray-600 italic">
                        "<?php echo htmlspecialchars($ayahData['translation']); ?>"
                    </div>
                </div>
                
                <!-- Quick Share Buttons -->
                <div class="flex items-center justify-center gap-3 pt-4 border-t border-gray-200">
                    <button onclick="shareToWhatsApp('<?php echo addslashes($ayahSharingData['whatsapp_text']); ?>')" 
                            class="flex items-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition duration-200">
                        <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                    </button>
                    <button onclick="shareToTelegram('<?php echo $ayahSharingData['url']; ?>', '<?php echo addslashes($ayahSharingData['telegram_text']); ?>')" 
                            class="flex items-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition duration-200">
                        <i class="fab fa-telegram mr-2"></i>Telegram
                    </button>
                    <button onclick="shareToFacebook('<?php echo $ayahSharingData['url']; ?>', '<?php echo addslashes($ayahSharingData['title']); ?>', '<?php echo addslashes($ayahSharingData['description']); ?>')" 
                            class="flex items-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg transition duration-200">
                        <i class="fab fa-facebook mr-2"></i>Facebook
                    </button>
                    <button onclick="shareToTwitter('<?php echo $ayahSharingData['url']; ?>', '<?php echo addslashes($ayahSharingData['title'] . ' #AlQuran #Ayat'); ?>')" 
                            class="flex items-center px-3 py-2 bg-sky-100 hover:bg-sky-200 text-sky-700 rounded-lg transition duration-200">
                        <i class="fab fa-twitter mr-2"></i>Twitter
                    </button>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Surah Overview Mode -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            Surah <?php echo $surahData['name_latin']; ?>
                        </h1>
                        <p class="text-gray-600">
                            <?php echo $surahData['meaning']; ?> • <?php echo $surahData['verses_count']; ?> Ayat • <?php echo $surahData['revelation_place']; ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php 
                        $surahSharingData = $sharingSystem->generateSurahSharing($surahNumber, $surahData);
                        ?>
                        <button onclick="openSharingModal('<?php echo addslashes(json_encode($surahSharingData)); ?>', 'surah')" 
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-share-alt mr-2"></i>Bagikan Surah
                        </button>
                    </div>
                </div>
                
                <!-- Surah Header -->
                <div class="text-center mb-6 p-6 bg-green-50 rounded-lg">
                    <div class="text-4xl font-arabic text-gray-800 mb-2">
                        <?php echo htmlspecialchars($surahData['name']); ?>
                    </div>
                    <div class="text-xl text-gray-600">
                        <?php echo $surahData['name_latin']; ?>
                    </div>
                </div>
                
                <!-- Sample Ayahs with Individual Sharing -->
                <div class="space-y-4">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <?php 
                        $sampleAyah = [
                            'number' => $i,
                            'text' => $i == 1 ? 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ' : 'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ',
                            'translation' => $i == 1 ? 'Dengan nama Allah Yang Maha Pengasih, Maha Penyayang' : 'Segala puji bagi Allah, Tuhan seluruh alam'
                        ];
                        $ayahSharingData = $sharingSystem->generateAyahSharing($surahNumber, $i, $sampleAyah, $surahData);
                        ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-3">
                                <span class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                                    <?php echo $i; ?>
                                </span>
                                <div class="flex items-center gap-2">
                                    <button onclick="copyToClipboard('<?php echo addslashes($ayahSharingData['copy_text']); ?>')" 
                                            class="text-gray-500 hover:text-gray-700 text-sm" 
                                            title="Salin ayat">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button onclick="openSharingModal('<?php echo addslashes(json_encode($ayahSharingData)); ?>', 'ayat')" 
                                            class="text-gray-500 hover:text-green-600 text-sm" 
                                            title="Bagikan ayat">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                    <a href="?surah=<?php echo $surahNumber; ?>&ayat=<?php echo $i; ?>" 
                                       class="text-green-600 hover:text-green-700 text-sm font-medium">
                                        <i class="fas fa-eye mr-1"></i>Detail
                                    </a>
                                </div>
                            </div>
                            
                            <div class="text-right text-xl font-arabic text-gray-800 mb-2 leading-relaxed" dir="rtl">
                                <?php echo htmlspecialchars($sampleAyah['text']); ?>
                            </div>
                            <div class="text-gray-600 italic">
                                "<?php echo htmlspecialchars($sampleAyah['translation']); ?>"
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <?php if ($surahNumber > 1): ?>
                        <a href="?surah=<?php echo $surahNumber - 1; ?>" 
                           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                            <i class="fas fa-chevron-left mr-2"></i>Surah Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($surahNumber < 114): ?>
                        <a href="?surah=<?php echo $surahNumber + 1; ?>" 
                           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                            Surah Selanjutnya<i class="fas fa-chevron-right ml-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="text-sm text-gray-600">
                    Surah <?php echo $surahNumber; ?> dari 114
                </div>
            </div>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>

    <!-- JavaScript -->
    <script src="../assets/js/islamic-content.js"></script>
    <script src="../assets/js/islamic-sharing.js"></script>
    
    <script>
        // Additional Al-Quran specific sharing functions
        function shareAyahRange(surahNumber, startAyah, endAyah) {
            const url = `${window.location.origin}${window.location.pathname}?surah=${surahNumber}&ayat=${startAyah}-${endAyah}`;
            const title = `QS. ${surahNumber}:${startAyah}-${endAyah}`;
            const description = `Ayat ${startAyah} sampai ${endAyah} dari Surah ke-${surahNumber}`;
            
            const sharingData = {
                url: url,
                title: title,
                description: description,
                whatsapp_text: `*${title}*\n\n${description}\n\n🔗 Baca selengkapnya: ${url}\n\n📱 Masjid Al-Muhajirin`,
                telegram_text: `*${title}*\n\n${description}\n\n🔗 [Baca selengkapnya](${url})\n\n📱 Masjid Al-Muhajirin`,
                facebook_url: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(title + '\n\n' + description)}`,
                twitter_url: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title + ' #AlQuran #Ayat')}&via=MasjidAlMuhajirin`,
                linkedin_url: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}&summary=${encodeURIComponent(description)}`,
                copy_text: `${title}\n\n${description}\n\nSumber: ${url}\n\nMasjid Al-Muhajirin`
            };
            
            openSharingModal(sharingData, 'ayat');
        }
        
        // Share entire Juz/Para
        function shareJuz(juzNumber) {
            const url = `${window.location.origin}${window.location.pathname}?juz=${juzNumber}`;
            const title = `Juz ${juzNumber} - Al-Quran`;
            const description = `Baca Juz ke-${juzNumber} dari Al-Quran Karim`;
            
            const sharingData = {
                url: url,
                title: title,
                description: description,
                whatsapp_text: `*${title}*\n\n${description}\n\n🔗 Baca selengkapnya: ${url}\n\n📱 Masjid Al-Muhajirin`,
                telegram_text: `*${title}*\n\n${description}\n\n🔗 [Baca selengkapnya](${url})\n\n📱 Masjid Al-Muhajirin`,
                facebook_url: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(title + '\n\n' + description)}`,
                twitter_url: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title + ' #AlQuran #Juz')}&via=MasjidAlMuhajirin`,
                linkedin_url: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}&summary=${encodeURIComponent(description)}`,
                copy_text: `${title}\n\n${description}\n\nSumber: ${url}\n\nMasjid Al-Muhajirin`
            };
            
            openSharingModal(sharingData, 'juz');
        }
    </script>
</body>
</html>