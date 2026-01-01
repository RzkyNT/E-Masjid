/**
 * Islamic Content Sharing JavaScript
 * Handles sharing modals and functionality for all Islamic content
 */

// Global sharing data storage
let currentSharingData = null;

/**
 * Open sharing modal with specific content data
 */
function openSharingModal(sharingDataJson, contentType = 'content') {
    try {
        // Check if input data is empty or invalid
        if (!sharingDataJson || sharingDataJson.trim() === '') {
            console.error('Empty sharing data provided');
            showNotification('Data sharing tidak tersedia', 'error');
            return;
        }
        
        // Handle both string and object inputs
        let sharingData;
        if (typeof sharingDataJson === 'string') {
            // Log the raw input for debugging
            console.log('Raw JSON input:', sharingDataJson.substring(0, 200) + '...');
            
            // Decode HTML entities first, then parse JSON
            let decodedJson = sharingDataJson
                .replace(/&quot;/g, '"')
                .replace(/&#039;/g, "'")
                .replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>');
            
            // Additional check for empty decoded JSON
            if (!decodedJson || decodedJson.trim() === '') {
                console.error('Empty decoded JSON data');
                showNotification('Data sharing tidak valid', 'error');
                return;
            }
            
            // Try to fix common JSON issues
            decodedJson = decodedJson
                .replace(/\n/g, '\\n')
                .replace(/\r/g, '\\r')
                .replace(/\t/g, '\\t')
                .replace(/\\/g, '\\\\'); // Escape backslashes
            
            try {
                sharingData = JSON.parse(decodedJson);
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Problematic JSON:', decodedJson.substring(0, 500));
                
                // Try to identify the problem area
                const errorPosition = parseError.message.match(/position (\d+)/);
                if (errorPosition) {
                    const pos = parseInt(errorPosition[1]);
                    const problemArea = decodedJson.substring(Math.max(0, pos - 50), pos + 50);
                    console.error('Problem area around position ' + pos + ':', problemArea);
                }
                
                showNotification('Format data sharing tidak valid. Periksa console untuk detail.', 'error');
                return;
            }
        } else {
            sharingData = sharingDataJson;
        }
        
        // Validate sharing data structure
        if (!sharingData || typeof sharingData !== 'object') {
            console.error('Invalid sharing data structure:', sharingData);
            showNotification('Format data sharing tidak valid', 'error');
            return;
        }
        
        // Ensure required fields exist
        if (!sharingData.url || !sharingData.title) {
            console.error('Missing required sharing data fields:', sharingData);
            showNotification('Data sharing tidak lengkap', 'error');
            return;
        }
        
        currentSharingData = sharingData;
        
        // Create modal if it doesn't exist
        if (!document.getElementById('sharing-modal')) {
            createSharingModal(currentSharingData, contentType);
        } else {
            updateSharingModal(currentSharingData, contentType);
        }
        
        // Show modal
        document.getElementById('sharing-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
    } catch (error) {
        console.error('Error opening sharing modal:', error);
        console.error('Input data:', sharingDataJson);
        console.error('Content type:', contentType);
        showNotification('Gagal membuka menu berbagi. Silakan coba lagi.', 'error');
    }
}

/**
 * Close sharing modal
 */
function closeSharingModal() {
    const modal = document.getElementById('sharing-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/**
 * Create sharing modal dynamically
 */
function createSharingModal(sharingData, contentType) {
    const modal = document.createElement('div');
    modal.id = 'sharing-modal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 hidden';
    modal.innerHTML = generateSharingModalContent(sharingData, contentType);
    document.body.appendChild(modal);
    
    // Add click outside to close
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeSharingModal();
        }
    });
}

/**
 * Update existing sharing modal
 */
function updateSharingModal(sharingData, contentType) {
    const modal = document.getElementById('sharing-modal');
    if (modal) {
        modal.innerHTML = generateSharingModalContent(sharingData, contentType);
    }
}

/**
 * Generate sharing modal content HTML
 */
function generateSharingModalContent(sharingData, contentType) {
    // Safely encode URLs and text for HTML attributes
    const safeEncode = (text) => {
        return encodeURIComponent(text || '').replace(/'/g, '%27').replace(/"/g, '%22');
    };
    
    const safeHtml = (text) => {
        return (text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    };
    
    return `
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-share-alt text-blue-600 mr-2"></i>
                            Bagikan ${capitalizeFirst(contentType)}
                        </h3>
                        <button onclick="closeSharingModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <!-- WhatsApp -->
                        <a href="https://wa.me/?text=${safeEncode(sharingData.whatsapp_text)}" 
                           target="_blank" 
                           class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                            <i class="fab fa-whatsapp text-green-600 text-xl mr-3"></i>
                            <span class="font-medium text-gray-900">WhatsApp</span>
                        </a>
                        
                        <!-- Telegram -->
                        <a href="https://t.me/share/url?url=${safeEncode(sharingData.url)}&text=${safeEncode(sharingData.telegram_text)}" 
                           target="_blank" 
                           class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <i class="fab fa-telegram text-blue-600 text-xl mr-3"></i>
                            <span class="font-medium text-gray-900">Telegram</span>
                        </a>
                        
                        <!-- Facebook -->
                        <a href="${safeHtml(sharingData.facebook_url)}" 
                           target="_blank" 
                           class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <i class="fab fa-facebook text-blue-700 text-xl mr-3"></i>
                            <span class="font-medium text-gray-900">Facebook</span>
                        </a>
                        
                        <!-- Twitter -->
                        <a href="${safeHtml(sharingData.twitter_url)}" 
                           target="_blank" 
                           class="flex items-center p-3 bg-sky-50 hover:bg-sky-100 rounded-lg transition duration-200">
                            <i class="fab fa-twitter text-sky-600 text-xl mr-3"></i>
                            <span class="font-medium text-gray-900">Twitter</span>
                        </a>
                        
                        <!-- LinkedIn -->
                        <a href="${safeHtml(sharingData.linkedin_url)}" 
                           target="_blank" 
                           class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200">
                            <i class="fab fa-linkedin text-blue-800 text-xl mr-3"></i>
                            <span class="font-medium text-gray-900">LinkedIn</span>
                        </a>
                        
                        <!-- Copy Link -->
                        <button onclick="copyToClipboard('${safeHtml(sharingData.url)}')" 
                                class="w-full flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-200">
                            <i class="fas fa-link text-gray-600 text-xl mr-3"></i>
                            <span class="font-medium text-gray-900">Salin Link</span>
                        </button>
                        
                        <!-- Copy Text -->
                        <button onclick="copyToClipboard(\`${(sharingData.copy_text || '').replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)" 
                                class="w-full flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-200">
                            <i class="fas fa-copy text-gray-600 text-xl mr-3"></i>
                            <span class="font-medium text-gray-900">Salin Teks</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Copy text to clipboard
 */
async function copyToClipboard(text) {
    try {
        // Decode HTML entities if present
        const decodedText = text.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&');
        
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(decodedText);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = decodedText;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            textArea.remove();
        }
        
        showNotification('Berhasil disalin ke clipboard!', 'success');
        closeSharingModal();
        
    } catch (error) {
        console.error('Failed to copy:', error);
        showNotification('Gagal menyalin ke clipboard', 'error');
    }
}

/**
 * Quick share functions for specific platforms
 */
function shareToWhatsApp(text) {
    const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}

function shareToTelegram(url, text) {
    const shareUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
    window.open(shareUrl, '_blank');
}

function shareToFacebook(url, title, description) {
    const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(title + '\n\n' + description)}`;
    window.open(shareUrl, '_blank');
}

function shareToTwitter(url, text) {
    const shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}&via=MasjidAlMuhajirin`;
    window.open(shareUrl, '_blank');
}

function shareToLinkedIn(url, title, description) {
    const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}&summary=${encodeURIComponent(description)}`;
    window.open(shareUrl, '_blank');
}

/**
 * Native Web Share API (if supported)
 */
async function nativeShare(sharingData) {
    if (navigator.share) {
        try {
            await navigator.share({
                title: sharingData.title,
                text: sharingData.description,
                url: sharingData.url
            });
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error sharing:', error);
                // Fallback to custom modal
                openSharingModal(sharingData);
            }
        }
    } else {
        // Fallback to custom modal
        openSharingModal(sharingData);
    }
}

/**
 * Generate sharing data for different content types
 */
function generateSharingData(contentType, contentData) {
    const baseUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
    
    switch (contentType) {
        case 'surah':
            return generateSurahSharingData(contentData, baseUrl);
        case 'ayah':
            return generateAyahSharingData(contentData, baseUrl);
        case 'hadits':
            return generateHaditsSharingData(contentData, baseUrl);
        case 'doa':
            return generateDoaSharingData(contentData, baseUrl);
        case 'asmaul_husna':
            return generateAsmaulHusnaSharingData(contentData, baseUrl);
        // case 'tafsir':
        //     return generateTafsirSharingData(contentData, baseUrl);
        // case 'dzikir':
        //     return generateDzikirSharingData(contentData, baseUrl);
        default:
            return generateGenericSharingData(contentData, baseUrl);
    }
}

/**
 * Helper functions for generating sharing data
 */
function generateSurahSharingData(data, baseUrl) {
    const url = `${baseUrl}/pages/alquran.php?surah=${data.number}`;
    const title = `Surah ${data.name_latin} - ${data.name}`;
    const description = `Baca Surah ${data.name_latin} (${data.verses_count} ayat) - ${data.meaning} dari Al-Quran`;
    
    return {
        url: url,
        title: title,
        description: description,
        whatsapp_text: `*${title}*\n\n${description}\n\n🔗 Baca selengkapnya: ${url}\n\n📱 Masjid Al-Muhajirin`,
        telegram_text: `*${title}*\n\n${description}\n\n🔗 [Baca selengkapnya](${url})\n\n📱 Masjid Al-Muhajirin`,
        facebook_url: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(title + '\n\n' + description)}`,
        twitter_url: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title + ' #AlQuran #Islam')}&via=MasjidAlMuhajirin`,
        linkedin_url: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}&summary=${encodeURIComponent(description)}`,
        copy_text: `${title}\n\n${description}\n\nSumber: ${url}\n\nMasjid Al-Muhajirin`
    };
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => notification.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification-toast fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300`;
    
    const colors = {
        success: 'bg-green-600 text-white',
        error: 'bg-red-600 text-white',
        info: 'bg-blue-600 text-white',
        warning: 'bg-yellow-600 text-white'
    };
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-times-circle',
        info: 'fas fa-info-circle',
        warning: 'fas fa-exclamation-triangle'
    };
    
    notification.className += ` ${colors[type] || colors.info}`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="${icons[type] || icons.info}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:opacity-75">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

/**
 * Utility functions
 */
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}

/**
 * Keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Close modal with Escape key
    if (e.key === 'Escape') {
        closeSharingModal();
    }
    
    // Share with Ctrl+Shift+S
    if (e.ctrlKey && e.shiftKey && e.key === 'S') {
        e.preventDefault();
        if (currentSharingData) {
            openSharingModal(currentSharingData);
        }
    }
});

/**
 * Initialize sharing system when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add sharing buttons to existing content if needed
    initializeSharingButtons();
});

/**
 * Initialize sharing buttons for existing content
 */
function initializeSharingButtons() {
    // This function can be used to add sharing buttons to existing content
    // that doesn't have them yet
    console.log('Islamic sharing system initialized');
}