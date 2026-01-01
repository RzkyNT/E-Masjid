/**
 * Islamic Content JavaScript Functions
 * For Masjid Al-Muhajirin Information System
 * 
 * Provides copy, share, and interaction functionality for Islamic content
 * Requirements: 6.3, 2.8
 */

/**
 * Copy Islamic content to clipboard
 * Requirements: 6.3, 2.8
 */
function copyIslamicContent(button) {
    const container = button.closest('.hadits-container, .doa-container, .asma-container, .asma-list-item');
    
    if (!container) {
        showButtonFeedback(button, 'error', '<i class="fas fa-times mr-1"></i>Gagal', 2000);
        return;
    }
    
    // Extract content based on container type
    let content = extractContentFromContainer(container);
    
    if (!content.text) {
        showButtonFeedback(button, 'error', '<i class="fas fa-times mr-1"></i>Gagal', 2000);
        return;
    }
    
    // Copy to clipboard
    navigator.clipboard.writeText(content.text).then(() => {
        showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Tersalin', 2000);
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = content.text;
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Tersalin', 2000);
        } catch (err) {
            showButtonFeedback(button, 'error', '<i class="fas fa-times mr-1"></i>Gagal', 2000);
        }
        
        document.body.removeChild(textArea);
    });
}

/**
 * Share Islamic content using Web Share API or fallback to copy
 * Requirements: 6.3, 2.8
 */
function shareIslamicContent(button) {
    const container = button.closest('.hadits-container, .doa-container, .asma-container, .asma-list-item');
    
    if (!container) {
        showButtonFeedback(button, 'error', '<i class="fas fa-times mr-1"></i>Gagal', 2000);
        return;
    }
    
    // Extract content based on container type
    let content = extractContentFromContainer(container);
    
    if (!content.text) {
        showButtonFeedback(button, 'error', '<i class="fas fa-times mr-1"></i>Gagal', 2000);
        return;
    }
    
    // Try Web Share API first
    if (navigator.share) {
        navigator.share({
            title: content.title,
            text: content.text,
            url: window.location.href
        }).then(() => {
            showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Dibagikan', 1500);
        }).catch((error) => {
            // User cancelled or error occurred
            if (error.name !== 'AbortError') {
                // Fallback to copy
                copyIslamicContent(button);
            }
        });
    } else {
        // Fallback to copy
        copyIslamicContent(button);
    }
}

/**
 * Extract content from container based on type
 */
function extractContentFromContainer(container) {
    let content = {
        text: '',
        title: '',
        type: ''
    };
    
    // Determine content type
    if (container.classList.contains('hadits-container')) {
        content.type = 'hadits';
        content.title = 'Hadits';
    } else if (container.classList.contains('doa-container')) {
        content.type = 'doa';
        content.title = 'Doa';
    } else if (container.classList.contains('asma-container') || container.classList.contains('asma-list-item')) {
        content.type = 'asma';
        content.title = 'Asmaul Husna';
    }
    
    // Extract Arabic text
    const arabicElement = container.querySelector('.arabic-text');
    const arabicText = arabicElement ? arabicElement.textContent.trim() : '';
    
    // Extract title/judul
    const titleElement = container.querySelector('h3');
    const titleText = titleElement ? titleElement.textContent.trim() : '';
    
    // Extract translation
    const translationElement = container.querySelector('.translation-text .text-base, .translation-text');
    let translationText = '';
    if (translationElement) {
        // Get only the translation text, not the label
        const textDiv = translationElement.querySelector('.text-base');
        translationText = textDiv ? textDiv.textContent.trim() : translationElement.textContent.replace(/^(Terjemahan|Artinya):\s*/, '').trim();
    }
    
    // Extract Latin transliteration (for Asmaul Husna)
    const latinElement = container.querySelector('.latin-text');
    let latinText = '';
    if (latinElement) {
        latinText = latinElement.textContent.replace(/^Transliterasi:\s*/, '').trim();
    }
    
    // Extract source information
    const sourceElement = container.querySelector('.text-gray-800, .font-medium');
    let sourceText = '';
    if (sourceElement && content.type === 'hadits') {
        sourceText = sourceElement.textContent.trim();
    }
    
    // Extract number/ID
    const numberElement = container.querySelector('.bg-green-600, .bg-purple-600, .bg-amber-600');
    const numberText = numberElement ? numberElement.textContent.trim() : '';
    
    // Build full text based on content type
    let fullText = '';
    
    if (content.type === 'hadits') {
        if (titleText) {
            fullText += titleText + '\n\n';
        }
        if (arabicText) {
            fullText += arabicText + '\n\n';
        }
        if (translationText) {
            fullText += translationText + '\n\n';
        }
        if (sourceText && numberText) {
            fullText += `(${sourceText}, Hadits ${numberText})`;
        } else if (numberText) {
            fullText += `(Hadits ${numberText})`;
        }
    } else if (content.type === 'doa') {
        if (titleText) {
            fullText += titleText + '\n\n';
        }
        if (arabicText) {
            fullText += arabicText + '\n\n';
        }
        if (translationText) {
            fullText += translationText + '\n\n';
        }
        if (numberText) {
            fullText += `(Doa ${numberText})`;
        }
    } else if (content.type === 'asma') {
        if (arabicText) {
            fullText += arabicText + '\n\n';
        }
        if (latinText) {
            fullText += latinText + '\n\n';
        }
        if (translationText) {
            fullText += translationText + '\n\n';
        }
        if (numberText) {
            fullText += `(Asmaul Husna ${numberText})`;
        }
    }
    
    content.text = fullText.trim();
    content.title = titleText || content.title;
    
    return content;
}

/**
 * Show feedback on button click
 * Requirements: 6.3
 */
function showButtonFeedback(button, type, message, duration) {
    const originalText = button.innerHTML;
    const originalClasses = button.className;
    
    button.innerHTML = message;
    
    if (type === 'success') {
        button.className = button.className.replace(/bg-\w+-\d+/, 'bg-green-100').replace(/text-\w+-\d+/, 'text-green-600');
    } else if (type === 'error') {
        button.className = button.className.replace(/bg-\w+-\d+/, 'bg-red-100').replace(/text-\w+-\d+/, 'text-red-600');
    }
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.className = originalClasses;
    }, duration);
}

/**
 * Font size control for Islamic content
 * Requirements: 6.2
 */
let islamicFontSize = 1; // 1 = 100%

const ISLAMIC_FONT_BASE = {
    arabic: 2.0,
    translation: 1.0,
    latin: 1.0
};

const ISLAMIC_LINE_HEIGHT = {
    arabic: 2.5,
    translation: 1.6,
    latin: 1.6
};

/**
 * Change font size for Islamic content
 * Requirements: 6.2
 */
function changeIslamicFontSize(action) {
    const indicator = document.getElementById('islamic-font-size-indicator');

    if (action === 'increase' && islamicFontSize < 2) {
        islamicFontSize += 0.1;
    } else if (action === 'decrease' && islamicFontSize > 0.6) {
        islamicFontSize -= 0.1;
    }

    applyIslamicFontSize();

    if (indicator) {
        indicator.textContent = Math.round(islamicFontSize * 100) + '%';
    }

    localStorage.setItem('islamic_font_size', islamicFontSize);
}

/**
 * Apply font size to Islamic content
 * Requirements: 6.2
 */
function applyIslamicFontSize() {
    document.querySelectorAll('.arabic-text').forEach(el => {
        el.style.fontSize = `${ISLAMIC_FONT_BASE.arabic * islamicFontSize}rem`;
        el.style.lineHeight = ISLAMIC_LINE_HEIGHT.arabic;
    });

    document.querySelectorAll('.translation-text').forEach(el => {
        el.style.fontSize = `${ISLAMIC_FONT_BASE.translation * islamicFontSize}rem`;
        el.style.lineHeight = ISLAMIC_LINE_HEIGHT.translation;
    });

    document.querySelectorAll('.latin-text').forEach(el => {
        el.style.fontSize = `${ISLAMIC_FONT_BASE.latin * islamicFontSize}rem`;
        el.style.lineHeight = ISLAMIC_LINE_HEIGHT.latin;
    });
}

/**
 * Reset Islamic content font size
 * Requirements: 6.2
 */
function resetIslamicFontSize() {
    islamicFontSize = 1;
    applyIslamicFontSize();

    const indicator = document.getElementById('islamic-font-size-indicator');
    if (indicator) {
        indicator.textContent = '100%';
    }

    localStorage.removeItem('islamic_font_size');
}

/**
 * Load saved Islamic content font size
 * Requirements: 6.2
 */
function loadIslamicFontSize() {
    const savedSize = localStorage.getItem('islamic_font_size');
    if (savedSize) {
        islamicFontSize = parseFloat(savedSize);
        applyIslamicFontSize();
        
        const indicator = document.getElementById('islamic-font-size-indicator');
        if (indicator) {
            indicator.textContent = Math.round(islamicFontSize * 100) + '%';
        }
    }
}

/**
 * Search functionality for Islamic content
 * Requirements: 2.7, 3.8
 */
function searchIslamicContent(query, contentType) {
    const containers = document.querySelectorAll(`.${contentType}-container, .${contentType}-list-item`);
    let visibleCount = 0;
    
    query = query.toLowerCase().trim();
    
    containers.forEach(container => {
        let shouldShow = false;
        
        if (!query) {
            shouldShow = true;
        } else {
            // Search in title
            const titleElement = container.querySelector('h3');
            if (titleElement && titleElement.textContent.toLowerCase().includes(query)) {
                shouldShow = true;
            }
            
            // Search in translation
            const translationElement = container.querySelector('.translation-text');
            if (translationElement && translationElement.textContent.toLowerCase().includes(query)) {
                shouldShow = true;
            }
            
            // Search in Latin text (for Asmaul Husna)
            const latinElement = container.querySelector('.latin-text');
            if (latinElement && latinElement.textContent.toLowerCase().includes(query)) {
                shouldShow = true;
            }
        }
        
        if (shouldShow) {
            container.style.display = '';
            visibleCount++;
        } else {
            container.style.display = 'none';
        }
    });
    
    // Update search results count
    const resultsElement = document.getElementById('search-results-count');
    if (resultsElement) {
        if (query) {
            resultsElement.textContent = `Ditemukan ${visibleCount} hasil untuk "${query}"`;
            resultsElement.style.display = 'block';
        } else {
            resultsElement.style.display = 'none';
        }
    }
    
    return visibleCount;
}

/**
 * Filter content by category
 * Requirements: 2.6, 6.7
 */
function filterContentByCategory(category, contentType) {
    const containers = document.querySelectorAll(`.${contentType}-container, .${contentType}-list-item`);
    let visibleCount = 0;
    
    containers.forEach(container => {
        let shouldShow = false;
        
        if (!category || category === 'all') {
            shouldShow = true;
        } else {
            // Check if container has the category
            const sourceElement = container.querySelector('.text-gray-800, .font-medium');
            if (sourceElement) {
                const sourceText = sourceElement.textContent.toLowerCase();
                if (sourceText.includes(category.toLowerCase())) {
                    shouldShow = true;
                }
            }
        }
        
        if (shouldShow) {
            container.style.display = '';
            visibleCount++;
        } else {
            container.style.display = 'none';
        }
    });
    
    return visibleCount;
}

/**
 * Toggle view layout (grid/list)
 * Requirements: 3.5
 */
function toggleViewLayout(layout) {
    const container = document.getElementById('content-container');
    if (!container) return;
    
    if (layout === 'grid') {
        container.classList.remove('list-view');
        container.classList.add('grid-view');
    } else {
        container.classList.remove('grid-view');
        container.classList.add('list-view');
    }
    
    // Update active button
    document.querySelectorAll('.view-toggle-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    const activeBtn = document.querySelector(`[data-layout="${layout}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    localStorage.setItem('islamic_content_layout', layout);
}

/**
 * Load saved view layout
 * Requirements: 3.5
 */
function loadViewLayout() {
    const savedLayout = localStorage.getItem('islamic_content_layout');
    if (savedLayout) {
        toggleViewLayout(savedLayout);
    }
}

/**
 * Bookmark functionality
 * Requirements: 3.7, 6.6
 */
function toggleBookmark(button, contentId, contentType) {
    const bookmarks = getBookmarks(contentType);
    const isBookmarked = bookmarks.includes(contentId);
    
    if (isBookmarked) {
        // Remove bookmark
        const index = bookmarks.indexOf(contentId);
        bookmarks.splice(index, 1);
        button.innerHTML = '<i class="fas fa-bookmark-o"></i>';
        button.classList.remove('bookmarked');
        showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Dihapus', 1500);
    } else {
        // Add bookmark
        bookmarks.push(contentId);
        button.innerHTML = '<i class="fas fa-bookmark"></i>';
        button.classList.add('bookmarked');
        showButtonFeedback(button, 'success', '<i class="fas fa-check mr-1"></i>Disimpan', 1500);
    }
    
    saveBookmarks(contentType, bookmarks);
}

/**
 * Get bookmarks from localStorage
 * Requirements: 3.7, 6.6
 */
function getBookmarks(contentType) {
    const saved = localStorage.getItem(`${contentType}_bookmarks`);
    return saved ? JSON.parse(saved) : [];
}

/**
 * Save bookmarks to localStorage
 * Requirements: 3.7, 6.6
 */
function saveBookmarks(contentType, bookmarks) {
    localStorage.setItem(`${contentType}_bookmarks`, JSON.stringify(bookmarks));
}

/**
 * Initialize Islamic content functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Load saved font size
    loadIslamicFontSize();
    
    // Load saved view layout
    loadViewLayout();
    
    // Initialize search functionality
    const searchInput = document.getElementById('islamic-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const contentType = this.dataset.contentType || 'hadits';
            searchIslamicContent(this.value, contentType);
        });
    }
    
    // Initialize category filter
    const categorySelect = document.getElementById('category-filter');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const contentType = this.dataset.contentType || 'doa';
            filterContentByCategory(this.value, contentType);
        });
    }
    
    // Initialize view toggle buttons
    document.querySelectorAll('.view-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const layout = this.dataset.layout;
            toggleViewLayout(layout);
        });
    });
    
    // Initialize bookmark buttons
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        const contentId = btn.dataset.contentId;
        const contentType = btn.dataset.contentType;
        const bookmarks = getBookmarks(contentType);
        
        if (bookmarks.includes(contentId)) {
            btn.innerHTML = '<i class="fas fa-bookmark"></i>';
            btn.classList.add('bookmarked');
        }
        
        btn.addEventListener('click', function() {
            toggleBookmark(this, contentId, contentType);
        });
    });
});

/**
 * Keyboard shortcuts for Islamic content
 */
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case '=':
            case '+':
                e.preventDefault();
                changeIslamicFontSize('increase');
                break;
            case '-':
                e.preventDefault();
                changeIslamicFontSize('decrease');
                break;
            case '0':
                e.preventDefault();
                resetIslamicFontSize();
                break;
        }
    }
});