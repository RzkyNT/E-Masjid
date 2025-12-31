<?php
/**
 * Reusable Modal Component for Friday Schedule Management
 * 
 * This component provides a flexible modal structure that can be used
 * for various purposes including viewing, adding, and editing Friday schedules.
 * 
 * Usage:
 * include 'includes/modal_component.php';
 * renderModal($modalId, $modalTitle, $modalContent, $modalFooter, $options);
 */

/**
 * Render a reusable modal component
 * 
 * @param string $modalId - Unique ID for the modal
 * @param string $modalTitle - Title displayed in modal header
 * @param string $modalContent - HTML content for modal body
 * @param string $modalFooter - HTML content for modal footer (buttons)
 * @param array $options - Additional options (size, closable, etc.)
 */
function renderModal($modalId, $modalTitle = '', $modalContent = '', $modalFooter = '', $options = []) {
    // Default options
    $defaults = [
        'size' => 'md', // sm, md, lg, xl
        'closable' => true,
        'backdrop_close' => true,
        'escape_close' => true,
        'animation' => true,
        'z_index' => 'z-50'
    ];
    
    $options = array_merge($defaults, $options);
    
    // Size classes
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md', 
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '4xl' => 'max-w-4xl'
    ];
    
    $modalSizeClass = $sizeClasses[$options['size']] ?? $sizeClasses['md'];
    $animationClass = $options['animation'] ? 'transition-all duration-300 ease-in-out' : '';
    
    echo "
    <!-- Modal: {$modalId} -->
    <div id=\"{$modalId}\" 
         class=\"fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center {$options['z_index']} {$animationClass}\"
         data-backdrop-close=\"" . ($options['backdrop_close'] ? 'true' : 'false') . "\"
         data-escape-close=\"" . ($options['escape_close'] ? 'true' : 'false') . "\">
        
        <!-- Modal Container -->
        <div class=\"bg-white rounded-lg shadow-xl {$modalSizeClass} w-full mx-4 max-h-screen overflow-y-auto {$animationClass}\"
             onclick=\"event.stopPropagation()\">
            
            <!-- Modal Header -->
            <div class=\"flex justify-between items-center p-6 border-b border-gray-200\">
                <h3 class=\"text-lg font-semibold text-gray-900\" id=\"{$modalId}-title\">
                    {$modalTitle}
                </h3>
                " . ($options['closable'] ? "
                <button type=\"button\" 
                        onclick=\"closeModal('{$modalId}')\" 
                        class=\"text-gray-400 hover:text-gray-600 transition-colors duration-200\">
                    <svg class=\"w-6 h-6\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\"></path>
                    </svg>
                </button>
                " : "") . "
            </div>
            
            <!-- Modal Body -->
            <div class=\"p-6\" id=\"{$modalId}-body\">
                {$modalContent}
            </div>
            
            <!-- Modal Footer -->
            " . ($modalFooter ? "
            <div class=\"flex justify-end space-x-3 p-6 border-t border-gray-200\" id=\"{$modalId}-footer\">
                {$modalFooter}
            </div>
            " : "") . "
        </div>
    </div>";
}

/**
 * Render modal with form structure for Friday Schedule
 * 
 * @param string $modalId - Unique ID for the modal
 * @param string $mode - 'view', 'add', 'edit'
 * @param array $eventData - Event data for edit/view mode
 * @param bool $isAdmin - Whether user has admin privileges
 */
function renderFridayScheduleModal($modalId, $mode = 'view', $eventData = [], $isAdmin = false) {
    $modalTitle = '';
    $readonly = '';
    
    switch($mode) {
        case 'add':
            $modalTitle = 'Tambah Agenda Jumat';
            break;
        case 'edit':
            $modalTitle = 'Edit Agenda Jumat';
            break;
        case 'view':
        default:
            $modalTitle = 'Detail Agenda Jumat';
            $readonly = $isAdmin ? '' : 'readonly';
            break;
    }
    
    // Form content
    $modalContent = "
    <form id=\"{$modalId}-form\" class=\"space-y-4\">
        <input type=\"hidden\" id=\"{$modalId}-id\" name=\"id\" value=\"" . ($eventData['id'] ?? '') . "\">
        <input type=\"hidden\" id=\"{$modalId}-mode\" name=\"mode\" value=\"{$mode}\">
        
        <!-- Tanggal Jumat -->
        <div>
            <label for=\"{$modalId}-friday_date\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Tanggal Jumat <span class=\"text-red-500\">*</span>
            </label>
            <input type=\"date\" 
                   id=\"{$modalId}-friday_date\" 
                   name=\"friday_date\" 
                   value=\"" . ($eventData['friday_date'] ?? '') . "\"
                   class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                   {$readonly} required>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-friday_date-error\"></div>
        </div>
        
        <!-- Waktu Sholat -->
        <div>
            <label for=\"{$modalId}-prayer_time\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Waktu Sholat <span class=\"text-red-500\">*</span>
            </label>
            <input type=\"time\" 
                   id=\"{$modalId}-prayer_time\" 
                   name=\"prayer_time\" 
                   value=\"" . ($eventData['prayer_time'] ?? '') . "\"
                   class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                   {$readonly} required>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-prayer_time-error\"></div>
        </div>
        
        <!-- Nama Imam -->
        <div>
            <label for=\"{$modalId}-imam_name\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Nama Imam <span class=\"text-red-500\">*</span>
            </label>
            <input type=\"text\" 
                   id=\"{$modalId}-imam_name\" 
                   name=\"imam_name\" 
                   value=\"" . ($eventData['imam_name'] ?? '') . "\"
                   class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                   placeholder=\"Masukkan nama imam\"
                   {$readonly} required>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-imam_name-error\"></div>
        </div>
        
        <!-- Nama Khotib -->
        <div>
            <label for=\"{$modalId}-khotib_name\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Nama Khotib <span class=\"text-red-500\">*</span>
            </label>
            <input type=\"text\" 
                   id=\"{$modalId}-khotib_name\" 
                   name=\"khotib_name\" 
                   value=\"" . ($eventData['khotib_name'] ?? '') . "\"
                   class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                   placeholder=\"Masukkan nama khotib\"
                   {$readonly} required>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-khotib_name-error\"></div>
        </div>
        
        <!-- Tema Khutbah -->
        <div>
            <label for=\"{$modalId}-khutbah_theme\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Tema Khutbah <span class=\"text-red-500\">*</span>
            </label>
            <input type=\"text\" 
                   id=\"{$modalId}-khutbah_theme\" 
                   name=\"khutbah_theme\" 
                   value=\"" . ($eventData['khutbah_theme'] ?? '') . "\"
                   class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                   placeholder=\"Masukkan tema khutbah\"
                   {$readonly} required>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-khutbah_theme-error\"></div>
        </div>
        
        <!-- Deskripsi Khutbah -->
        <div>
            <label for=\"{$modalId}-khutbah_description\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Deskripsi Khutbah
            </label>
            <textarea id=\"{$modalId}-khutbah_description\" 
                      name=\"khutbah_description\" 
                      rows=\"3\"
                      class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                      placeholder=\"Masukkan deskripsi khutbah (opsional)\"
                      {$readonly}>" . ($eventData['khutbah_description'] ?? '') . "</textarea>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-khutbah_description-error\"></div>
        </div>
        
        <!-- Lokasi -->
        <div>
            <label for=\"{$modalId}-location\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Lokasi
            </label>
            <input type=\"text\" 
                   id=\"{$modalId}-location\" 
                   name=\"location\" 
                   value=\"" . ($eventData['location'] ?? 'Masjid Al-Muhajirin') . "\"
                   class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                   placeholder=\"Masukkan lokasi\"
                   {$readonly}>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-location-error\"></div>
        </div>
        
        <!-- Catatan Khusus -->
        <div>
            <label for=\"{$modalId}-special_notes\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Catatan Khusus
            </label>
            <textarea id=\"{$modalId}-special_notes\" 
                      name=\"special_notes\" 
                      rows=\"2\"
                      class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                      placeholder=\"Masukkan catatan khusus (opsional)\"
                      {$readonly}>" . ($eventData['special_notes'] ?? '') . "</textarea>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-special_notes-error\"></div>
        </div>
        
        <!-- Status (untuk edit mode) -->
        " . ($mode === 'edit' ? "
        <div>
            <label for=\"{$modalId}-status\" class=\"block text-sm font-medium text-gray-700 mb-2\">
                Status
            </label>
            <select id=\"{$modalId}-status\" 
                    name=\"status\" 
                    class=\"w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500\"
                    {$readonly}>
                <option value=\"scheduled\" " . (($eventData['status'] ?? '') === 'scheduled' ? 'selected' : '') . ">Terjadwal</option>
                <option value=\"completed\" " . (($eventData['status'] ?? '') === 'completed' ? 'selected' : '') . ">Selesai</option>
                <option value=\"cancelled\" " . (($eventData['status'] ?? '') === 'cancelled' ? 'selected' : '') . ">Dibatalkan</option>
            </select>
            <div class=\"text-red-500 text-sm mt-1 hidden\" id=\"{$modalId}-status-error\"></div>
        </div>
        " : "") . "
        
        <!-- Loading indicator -->
        <div id=\"{$modalId}-loading\" class=\"hidden text-center py-4\">
            <div class=\"inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-green-600\"></div>
            <span class=\"ml-2 text-gray-600\">Memproses...</span>
        </div>
        
        <!-- Error message -->
        <div id=\"{$modalId}-error\" class=\"hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg\">
            <div class=\"flex\">
                <svg class=\"w-5 h-5 mr-2 mt-0.5\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                    <path fill-rule=\"evenodd\" d=\"M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z\" clip-rule=\"evenodd\"></path>
                </svg>
                <span id=\"{$modalId}-error-message\"></span>
            </div>
        </div>
    </form>";
    
    // Footer buttons based on mode and permissions
    $modalFooter = '';
    if ($mode === 'view') {
        $modalFooter = "
        <button type=\"button\" 
                onclick=\"closeModal('{$modalId}')\" 
                class=\"px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200\">
            Tutup
        </button>";
    } elseif ($mode === 'add' && $isAdmin) {
        $modalFooter = "
        <button type=\"button\" 
                onclick=\"closeModal('{$modalId}')\" 
                class=\"px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200\">
            Batal
        </button>
        <button type=\"button\" 
                onclick=\"saveFridaySchedule('{$modalId}')\" 
                class=\"px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200\">
            Simpan
        </button>";
    } elseif ($mode === 'edit' && $isAdmin) {
        $modalFooter = "
        <button type=\"button\" 
                onclick=\"closeModal('{$modalId}')\" 
                class=\"px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200\">
            Batal
        </button>
        <button type=\"button\" 
                onclick=\"deleteFridaySchedule('{$modalId}')\" 
                class=\"px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200\">
            Hapus
        </button>
        <button type=\"button\" 
                onclick=\"saveFridaySchedule('{$modalId}')\" 
                class=\"px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200\">
            Simpan Perubahan
        </button>";
    }
    
    // Render the modal
    renderModal($modalId, $modalTitle, $modalContent, $modalFooter, [
        'size' => 'lg',
        'closable' => true,
        'backdrop_close' => true,
        'escape_close' => true
    ]);
}
?>