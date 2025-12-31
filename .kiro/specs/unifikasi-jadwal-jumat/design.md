# Design Document: Unifikasi Halaman Jadwal Jumat

## Overview

Desain ini menggabungkan halaman jadwal Jumat yang terpisah (`jadwal_jumat.php` dan `jadwal_jumat_calendar.php`) menjadi satu halaman terintegrasi dengan interface yang clean dan user-friendly. Halaman baru akan menampilkan toggle view antara card layout dan calendar layout, dengan modal popup untuk manajemen agenda yang dapat diakses dengan mengklik grid calendar.

## Architecture

### Current State Analysis
- **Public Pages**: `pages/jadwal_jumat.php` (card view), `pages/jadwal_jumat_calendar.php` (calendar view)
- **Admin Page**: `admin/masjid/jadwal_jumat.php` (list + form view)
- **API Endpoints**: `api/friday_schedule_events.php`, `api/friday_schedule_crud.php`
- **Database**: `friday_schedules` table dengan struktur lengkap

### Target Architecture
```
Unified Friday Schedule System
├── Public Interface (pages/jadwal_jumat.php)
│   ├── Card View (default)
│   ├── Calendar View (toggle)
│   └── Event Detail Modal (read-only)
├── Admin Interface (admin/masjid/jadwal_jumat.php)
│   ├── Calendar View dengan CRUD Modal
│   ├── List View (existing)
│   └── Drag & Drop Support
└── Shared Components
    ├── Modal Component (reusable)
    ├── Event Renderer
    └── API Integration Layer
```

## Components and Interfaces

### 1. Unified Public Page Component
**File**: `pages/jadwal_jumat.php` (modified)

**Features**:
- Toggle button untuk switch antara Card View dan Calendar View
- Card View: Grid layout dengan card design yang sudah ada
- Calendar View: FullCalendar integration dengan event click untuk detail modal
- Responsive design untuk mobile dan desktop
- Export iCal functionality

**Interface Structure**:
```php
class UnifiedFridaySchedulePage {
    private $viewMode = 'card'; // 'card' atau 'calendar'
    
    public function renderHeader()
    public function renderViewToggle()
    public function renderCardView()
    public function renderCalendarView()
    public function renderEventModal()
}
```

### 2. Enhanced Admin Interface
**File**: `admin/masjid/jadwal_jumat.php` (modified)

**Features**:
- Default view: Calendar dengan click-to-add functionality
- List view tetap tersedia untuk bulk management
- Modal popup untuk add/edit agenda dengan form validation
- Drag & drop untuk reschedule (menggunakan API yang sudah ada)
- Real-time calendar update setelah CRUD operations

**Modal Form Fields**:
- Tanggal Jumat (auto-filled dari clicked date)
- Waktu Sholat
- Nama Imam (dengan autocomplete)
- Nama Khotib (dengan autocomplete)
- Tema Khutbah (dengan suggestions)
- Deskripsi Khutbah
- Lokasi
- Catatan Khusus
- Status (untuk edit mode)

### 3. Reusable Modal Component
**Component**: `FridayScheduleModal`

**Props**:
- `mode`: 'view', 'add', 'edit'
- `eventData`: object dengan data agenda
- `isAdmin`: boolean untuk menentukan read-only atau editable
- `onSave`: callback function untuk save operation
- `onClose`: callback function untuk close modal

### 4. API Integration Layer
**Existing APIs** (tetap digunakan):
- `api/friday_schedule_events.php`: Fetch events untuk calendar
- `api/friday_schedule_crud.php`: CRUD operations
- `api/friday_schedule_ical.php`: Export functionality

**New API Enhancement**:
- Add `quick_add` action untuk simplified form dari calendar click
- Enhanced error handling dan validation messages

## Data Models

### Friday Schedule Model (Existing)
```sql
friday_schedules {
    id: INT PRIMARY KEY
    friday_date: DATE UNIQUE
    prayer_time: TIME
    imam_name: VARCHAR(100)
    khotib_name: VARCHAR(100)
    khutbah_theme: VARCHAR(255)
    khutbah_description: TEXT
    location: VARCHAR(255)
    special_notes: TEXT
    status: ENUM('scheduled', 'completed', 'cancelled')
    created_by: INT
    created_at: TIMESTAMP
    updated_at: TIMESTAMP
}
```

### Frontend Data Models
```javascript
// Event object untuk FullCalendar
const FridayEvent = {
    id: number,
    title: string,
    start: string, // ISO date
    allDay: boolean,
    backgroundColor: string,
    extendedProps: {
        prayer_time: string,
        imam_name: string,
        khotib_name: string,
        khutbah_theme: string,
        khutbah_description: string,
        location: string,
        special_notes: string,
        status: string,
        schedule_status: string
    }
}

// Modal state object
const ModalState = {
    isOpen: boolean,
    mode: 'view' | 'add' | 'edit',
    eventData: FridayEvent | null,
    selectedDate: string | null,
    isLoading: boolean,
    errors: object
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

Setelah menganalisis semua acceptance criteria, beberapa properties dapat dikombinasikan untuk menghindari redundansi:
- Properties tentang API compatibility (4.4, 6.2) dapat digabung menjadi satu property comprehensive
- Properties tentang data preservation (1.3, 4.3, 6.1) dapat dikombinasikan
- Properties tentang calendar functionality (5.1, 5.2, 5.3) dapat dikelompokkan

### Core Properties

**Property 1: View Toggle Functionality**
*For any* user session, switching between card view and calendar view should preserve all loaded data and maintain the current state without requiring a page reload
**Validates: Requirements 1.4**

**Property 2: Calendar Click Interaction**
*For any* calendar grid cell representing a Friday date, clicking on it should trigger the appropriate modal (add modal for empty dates, edit modal for dates with existing events)
**Validates: Requirements 2.1, 2.3**

**Property 3: Real-time Calendar Updates**
*For any* CRUD operation (create, update, delete) performed through the modal, the calendar display should immediately reflect the changes without requiring manual refresh
**Validates: Requirements 2.4**

**Property 4: Form Validation Consistency**
*For any* input data submitted through the agenda modal, invalid data should be rejected with appropriate error messages, and valid data should be accepted and processed successfully
**Validates: Requirements 2.5**

**Property 5: Responsive Layout Adaptation**
*For any* screen size or device orientation, the interface should adapt appropriately while maintaining all functionality and readability
**Validates: Requirements 3.4**

**Property 6: API Backward Compatibility**
*For any* existing API endpoint used by the current system, the unified system should continue to use the same endpoints with the same request/response format
**Validates: Requirements 4.4, 6.2**

**Property 7: Data Preservation**
*For any* existing Friday schedule data, the unified system should display and manage the data identically to the previous separate systems
**Validates: Requirements 1.3, 4.3, 6.1**

**Property 8: Calendar Event Accuracy**
*For any* Friday schedule in the database, the calendar should display the event on the correct date with accurate information
**Validates: Requirements 5.1**

**Property 9: Visual Event Indicators**
*For any* date on the calendar, dates with scheduled events should display visual indicators, and dates without events should not display indicators
**Validates: Requirements 5.2**

**Property 10: Calendar Navigation**
*For any* month navigation action (previous/next), the calendar should correctly load and display events for the target month
**Validates: Requirements 5.3**

**Property 11: Friday Highlighting**
*For any* calendar month view, all Friday dates should be visually highlighted regardless of whether they have scheduled events
**Validates: Requirements 5.5**

**Property 12: Export Functionality Preservation**
*For any* export iCal request, the system should generate a valid iCal file containing all scheduled Friday events with the same format as the previous system
**Validates: Requirements 6.4**

## Error Handling

### Client-Side Error Handling
- **Network Errors**: Display user-friendly messages when API calls fail
- **Validation Errors**: Show inline validation messages for form fields
- **Calendar Loading Errors**: Display fallback message when calendar fails to load
- **Modal Errors**: Handle modal state errors gracefully

### Server-Side Error Handling
- **Database Errors**: Log errors and return appropriate HTTP status codes
- **Permission Errors**: Return 403 Forbidden for unauthorized actions
- **Validation Errors**: Return structured error messages for client processing
- **Duplicate Date Errors**: Handle unique constraint violations for Friday dates

### Error Recovery Strategies
- **Auto-retry**: Implement retry logic for transient network errors
- **Graceful Degradation**: Fall back to list view if calendar fails to load
- **State Recovery**: Preserve form data when validation errors occur
- **User Feedback**: Provide clear instructions for error resolution

## Testing Strategy

### Dual Testing Approach
The system will be tested using both unit tests and property-based tests to ensure comprehensive coverage:

**Unit Tests** will focus on:
- Specific UI interactions (modal open/close, view toggle)
- API endpoint responses with known data
- Form validation with specific invalid inputs
- Error handling scenarios
- Integration points between components

**Property-Based Tests** will focus on:
- Universal properties across all valid inputs
- Calendar rendering accuracy with randomized schedule data
- Form validation with generated test data
- API compatibility with various request formats
- Responsive behavior across different screen sizes

### Property-Based Testing Configuration
- **Testing Library**: Use PHPUnit with property-based testing extensions for backend, Jest with fast-check for frontend JavaScript
- **Test Iterations**: Minimum 100 iterations per property test
- **Test Tagging**: Each property test must reference its design document property
- **Tag Format**: **Feature: unifikasi-jadwal-jumat, Property {number}: {property_text}**

### Testing Coverage Areas
1. **Frontend Components**: Modal behavior, calendar interactions, view switching
2. **API Integration**: CRUD operations, data fetching, error handling
3. **Data Consistency**: Database operations, state management
4. **User Experience**: Responsive design, loading states, error messages
5. **Backward Compatibility**: Existing functionality preservation

### Performance Testing
- **Page Load Time**: Unified page should load within 2 seconds
- **Calendar Rendering**: Should handle 12 months of data without performance degradation
- **Modal Response Time**: Modal should open/close within 300ms
- **API Response Time**: CRUD operations should complete within 1 second
