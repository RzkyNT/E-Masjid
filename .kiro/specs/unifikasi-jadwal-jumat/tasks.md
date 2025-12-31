# Implementation Plan: Unifikasi Halaman Jadwal Jumat

## Overview

Implementasi ini akan menggabungkan halaman jadwal Jumat yang terpisah menjadi satu halaman terintegrasi dengan toggle view dan modal popup untuk manajemen agenda. Fokus pada clean code, minimal dependencies, dan user experience yang optimal.

## Tasks

- [x] 1. Backup dan analisis file existing
  - Backup file `pages/jadwal_jumat.php` dan `pages/jadwal_jumat_calendar.php`
  - Backup file `admin/masjid/jadwal_jumat.php`
  - Dokumentasi fungsi-fungsi yang akan dipertahankan
  - _Requirements: 4.3, 6.1_

- [x] 2. Buat komponen modal reusable
  - [x] 2.1 Implementasi base modal component dengan HTML/CSS
    - Buat struktur HTML modal yang responsive
    - Implementasi CSS untuk styling modal (overlay, animation, responsive)
    - _Requirements: 2.2, 3.1, 3.4_

  - [x] 2.2 Write property test untuk modal component
    - **Property 1: View Toggle Functionality**
    - **Validates: Requirements 1.4**

  - [x] 2.3 Implementasi JavaScript untuk modal behavior
    - Fungsi open/close modal dengan animation
    - Event handling untuk click outside dan ESC key
    - State management untuk modal content
    - _Requirements: 2.1, 2.3, 2.4_

  - [x] 2.4 Write unit tests untuk modal JavaScript
    - Test modal open/close functionality
    - Test event handling
    - _Requirements: 2.1, 2.3_

- [x] 3. Refactor halaman publik (pages/jadwal_jumat.php)
  - [x] 3.1 Implementasi view toggle functionality
    - Tambah toggle button untuk switch card/calendar view
    - Implementasi JavaScript untuk switch view tanpa reload
    - Preserve state saat switch view
    - _Requirements: 1.1, 1.2, 1.4_

  - [x] 3.2 Write property test untuk view toggle
    - **Property 1: View Toggle Functionality**
    - **Validates: Requirements 1.4**

  - [x] 3.3 Integrasikan FullCalendar ke dalam halaman existing
    - Import FullCalendar library (minimal setup)
    - Konfigurasi calendar dengan API endpoint existing
    - Implementasi event click untuk modal detail (read-only)
    - _Requirements: 1.1, 5.1, 5.2, 5.3_

  - [x] 3.4 Write property test untuk calendar integration
    - **Property 8: Calendar Event Accuracy**
    - **Validates: Requirements 5.1**

  - [x] 3.5 Implementasi responsive design dan loading states
    - Pastikan layout responsive untuk mobile/desktop
    - Tambah loading indicators saat fetch data
    - Optimasi performa untuk minimal JavaScript
    - _Requirements: 3.3, 3.4, 3.5_

  - [x] 3.6 Write property test untuk responsive behavior
    - **Property 5: Responsive Layout Adaptation**
    - **Validates: Requirements 3.4**

- [ ] 4. Checkpoint - Test halaman publik
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Refactor halaman admin (admin/masjid/jadwal_jumat.php)
  - [x] 5.1 Implementasi calendar view sebagai default
    - Ganti default view dari list ke calendar
    - Integrasikan FullCalendar dengan CRUD functionality
    - Implementasi click-to-add pada empty calendar cells
    - _Requirements: 2.1, 5.1_

  - [x] 5.2 Write property test untuk admin calendar click
    - **Property 2: Calendar Click Interaction**
    - **Validates: Requirements 2.1, 2.3**

  - [x] 5.3 Implementasi modal CRUD untuk admin
    - Integrasikan modal component untuk add/edit agenda
    - Form validation dengan error handling
    - Auto-fill tanggal dari clicked calendar cell
    - _Requirements: 2.2, 2.5_

  - [x] 5.4 Write property test untuk form validation
    - **Property 4: Form Validation Consistency**
    - **Validates: Requirements 2.5**

  - [x] 5.5 Implementasi real-time calendar update
    - Update calendar setelah save/delete operations
    - Refresh calendar events tanpa page reload
    - Handle API errors dengan user feedback
    - _Requirements: 2.4_

  - [x] 5.6 Write property test untuk real-time updates
    - **Property 3: Real-time Calendar Updates**
    - **Validates: Requirements 2.4**

- [x] 6. Implementasi fitur calendar interaktif
  - [x] 6.1 Implementasi Friday highlighting
    - Highlight semua hari Jumat di calendar
    - Visual indicators untuk dates dengan events
    - Hover tooltips untuk event preview
    - _Requirements: 5.2, 5.4, 5.5_

  - [x] 6.2 Write property test untuk visual indicators
    - **Property 9: Visual Event Indicators**
    - **Validates: Requirements 5.2**
    - **Property 11: Friday Highlighting**
    - **Validates: Requirements 5.5**

  - [x] 6.3 Implementasi calendar navigation
    - Month navigation (prev/next buttons)
    - Proper event loading untuk different months
    - Maintain calendar state during navigation
    - _Requirements: 5.3_

  - [x] 6.4 Write property test untuk calendar navigation
    - **Property 10: Calendar Navigation**
    - **Validates: Requirements 5.3**

- [x] 7. Ensure API compatibility dan data preservation
  - [x] 7.1 Verify API endpoint compatibility
    - Test semua existing API calls masih berfungsi
    - Ensure response format tetap sama
    - Validate database queries tidak berubah
    - _Requirements: 4.4, 6.2, 4.5_

  - [x] 7.2 Write property test untuk API compatibility
    - **Property 6: API Backward Compatibility**
    - **Validates: Requirements 4.4, 6.2**

  - [x] 7.3 Verify data preservation
    - Test semua existing data masih accessible
    - Validate export iCal functionality
    - Check notification system integration
    - _Requirements: 6.1, 6.4, 6.5_

  - [x] 7.4 Write property test untuk data preservation
    - **Property 7: Data Preservation**
    - **Validates: Requirements 1.3, 4.3, 6.1**
    - **Property 12: Export Functionality Preservation**
    - **Validates: Requirements 6.4**

- [x] 8. Cleanup dan file consolidation
  - [x] 8.1 Remove atau rename file lama
    - Backup `pages/jadwal_jumat_calendar.php` 
    - Update navigation links yang mengarah ke file lama
    - Ensure no broken links dalam aplikasi
    - _Requirements: 4.1, 4.2_

  - [x] 8.2 Update dokumentasi dan comments
    - Update inline comments dalam code
    - Document new modal component usage
    - Update any configuration files if needed
    - _Requirements: 4.1, 4.2_

- [ ] 9. Final testing dan validation
  - [ ] 9.1 Write integration tests
    - Test end-to-end user workflows
    - Test admin CRUD operations
    - Test public view functionality
    - _Requirements: 1.3, 2.4, 3.4_

  - [ ] 9.2 Performance testing
    - Test page load times
    - Test calendar rendering dengan large datasets
    - Validate minimal JavaScript library usage
    - _Requirements: 3.5_

- [ ] 10. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked dengan testing sekarang menjadi required untuk comprehensive implementation
- Each task references specific requirements for traceability
- Focus on preserving existing functionality while adding new unified interface
- Minimal JavaScript libraries approach - only FullCalendar as external dependency
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- All existing API endpoints and database schema remain unchanged