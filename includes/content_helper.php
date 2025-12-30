<?php
/**
 * Content Helper Functions
 * Handles content processing for Quill.js and other rich text content
 */

/**
 * Safely render HTML content from Quill.js editor
 * @param string $content Raw content from database
 * @return string Safe HTML content
 */
function renderQuillContent($content) {
    if (empty($content)) {
        return '';
    }
    
    // Check if content is HTML (contains HTML tags)
    if (strip_tags($content) !== $content) {
        // Content contains HTML - process as Quill content
        return sanitizeQuillHTML($content);
    } else {
        // Content is plain text - convert line breaks
        return nl2br(htmlspecialchars($content));
    }
}

/**
 * Sanitize HTML content from Quill.js editor
 * @param string $html Raw HTML content
 * @return string Sanitized HTML content
 */
function sanitizeQuillHTML($html) {
    // Allowed HTML tags from Quill.js
    $allowed_tags = [
        'p', 'br', 'strong', 'em', 'u', 's', 'strike',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a', 'img',
        'span', 'div'
    ];
    
    // Allowed attributes
    $allowed_attributes = [
        'href', 'target', 'rel',
        'src', 'alt', 'width', 'height',
        'class', 'style',
        'data-*'
    ];
    
    // Basic HTML sanitization
    $html = strip_tags($html, '<' . implode('><', $allowed_tags) . '>');
    
    // Remove potentially dangerous attributes
    $html = preg_replace('/on\w+="[^"]*"/i', '', $html); // Remove onclick, onload, etc.
    $html = preg_replace('/javascript:/i', '', $html); // Remove javascript: URLs
    
    // Clean up Quill-specific formatting
    $html = cleanQuillFormatting($html);
    
    return $html;
}

/**
 * Clean up Quill.js specific formatting for better display
 * @param string $html HTML content from Quill
 * @return string Cleaned HTML content
 */
function cleanQuillFormatting($html) {
    // Remove empty paragraphs that Quill sometimes creates
    $html = preg_replace('/<p[^>]*><br[^>]*><\/p>/i', '', $html);
    $html = preg_replace('/<p[^>]*>\s*<\/p>/i', '', $html);
    
    // Convert Quill's color formatting to safer CSS classes
    $html = preg_replace_callback('/style="color:\s*([^"]+)"/i', function($matches) {
        $color = trim($matches[1]);
        // Convert common colors to CSS classes
        $color_map = [
            'red' => 'text-red-600',
            'blue' => 'text-blue-600',
            'green' => 'text-green-600',
            'yellow' => 'text-yellow-600',
            'purple' => 'text-purple-600',
            'gray' => 'text-gray-600',
            'black' => 'text-black',
        ];
        
        if (isset($color_map[$color])) {
            return 'class="' . $color_map[$color] . '"';
        }
        
        // For hex colors, keep the style but sanitize
        if (preg_match('/^#[0-9a-f]{6}$/i', $color)) {
            return 'style="color: ' . $color . '"';
        }
        
        // Remove unrecognized colors
        return '';
    }, $html);
    
    // Clean up background colors
    $html = preg_replace('/style="background-color:\s*[^"]+"/i', '', $html);
    
    // Ensure proper paragraph spacing
    $html = str_replace('<p>', '<p class="mb-4">', $html);
    
    // Style headings
    $html = str_replace('<h1>', '<h1 class="text-3xl font-bold mb-4 mt-6">', $html);
    $html = str_replace('<h2>', '<h2 class="text-2xl font-bold mb-3 mt-5">', $html);
    $html = str_replace('<h3>', '<h3 class="text-xl font-bold mb-3 mt-4">', $html);
    $html = str_replace('<h4>', '<h4 class="text-lg font-bold mb-2 mt-3">', $html);
    
    // Style lists
    $html = str_replace('<ul>', '<ul class="list-disc list-inside mb-4 ml-4">', $html);
    $html = str_replace('<ol>', '<ol class="list-decimal list-inside mb-4 ml-4">', $html);
    $html = str_replace('<li>', '<li class="mb-1">', $html);
    
    // Style blockquotes
    $html = str_replace('<blockquote>', '<blockquote class="border-l-4 border-green-500 pl-4 py-2 mb-4 bg-green-50 italic">', $html);
    
    // Style links
    $html = preg_replace('/<a([^>]*)>/i', '<a$1 class="text-green-600 hover:text-green-800 underline">', $html);
    
    // Style code blocks
    $html = str_replace('<pre>', '<pre class="bg-gray-100 p-4 rounded-lg mb-4 overflow-x-auto">', $html);
    $html = str_replace('<code>', '<code class="bg-gray-100 px-2 py-1 rounded text-sm">', $html);
    
    return $html;
}

/**
 * Generate excerpt from Quill content
 * @param string $content Full content
 * @param int $length Maximum length of excerpt
 * @return string Excerpt text
 */
function generateExcerpt($content, $length = 160) {
    // Strip HTML tags to get plain text
    $text = strip_tags($content);
    
    // Remove extra whitespace
    $text = preg_replace('/\s+/', ' ', trim($text));
    
    // Truncate to specified length
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        // Find the last complete word
        $last_space = strrpos($text, ' ');
        if ($last_space !== false) {
            $text = substr($text, 0, $last_space);
        }
        $text .= '...';
    }
    
    return $text;
}

/**
 * Estimate reading time for content
 * @param string $content Full content
 * @param int $wpm Words per minute (default: 200)
 * @return int Reading time in minutes
 */
function estimateReadingTime($content, $wpm = 200) {
    $text = strip_tags($content);
    $word_count = str_word_count($text);
    $reading_time = ceil($word_count / $wpm);
    
    return max(1, $reading_time); // Minimum 1 minute
}
?>