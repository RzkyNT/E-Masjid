# Analisis Fungsi-Fungsi yang Akan Dipertahankan
## Unifikasi Halaman Jadwal Jumat

**Tanggal Backup:** <?php echo date('Y-m-d H:i:s'); ?>
**Requirements:** 4.3, 6.1

## File yang Di-backup

1. `pages/jadwal_jumat.php` → `jadwal_jumat_original.php`
2. `pages/jadwal_jumat_calendar.php` → `jadwal_jumat_calendar_original.php`  
3. `admin/masjid/jadwal_jumat.php` → `admin_jadwal_jumat_original.php`

## Analisis Fungsi per File

### 1. pages/jadwal_jumat.php (Card View)

#### Fungsi Utama yang HARUS Dipertahankan:
- **Database Query Functions:**
  - Query untuk mendapatkan 8 jadwal Jumat mendatang
  - Query untuk jadwal hari ini (jika hari Jumat)
  - Query untuk jadwal Jumat berikutnya
  - Penanganan error dengan try-catch PDO

- **Helper Functions:**
  - `getIndonesianDayName($date)` - Konversi nama hari ke bahasa Indonesia
  - `getIndonesianDate($date)` - Format tanggal Indonesia (dd Bulan yyyy)
  - `getContactInfo()` - Informasi kontak masjid

- **Display Logic:**
  - Highlight jadwal hari ini dengan ring-2 ring-blue-500
  - Status badge untuk "Hari Ini"
  - Card layout dengan grid responsive (md:grid-cols-2)
  - Special notes dengan styling yellow-50 border-yellow-200

- **UI Components:**
  - Hero section dengan gradient green
  - Today's schedule highlight (blue background)
  - Next Friday highlight (green background)
  - Information section dengan 3 kolom info
  - Auto-refresh script (30 menit)

#### Data Structure:
```php
$friday_schedules = [
    'id', 'friday_date', 'prayer_time', 'imam_name', 'khotib_name',
    'khutbah_theme', 'khutbah_description', 'location', 'special_notes',
    'status', 'schedule_status' (today/upcoming/past)
]
```

### 2. pages/jadwal_jumat_calendar.php (Calendar View)

#### Fungsi Utama yang HARUS Dipertahankan:
- **FullCalendar Integration:**
  - FullCalendar v6.1.10 setup
  - Event fetching dari `../api/friday_schedule_events.php`
  - Indonesian locale ('id')
  - Friday highlighting (background #f0fdf4)

- **Modal System:**
  - Event detail modal dengan ID `eventModal`
  - Modal content generation dari event data
  - Close modal functionality (click outside, ESC, button)

- **View Toggle:**
  - Month view (`dayGridMonth`) dan List view (`listMonth`)
  - Button styling dengan active state management

- **Export Functionality:**
  - iCal export link ke `../api/friday_schedule_ical.php`
  - Legend dengan color coding (green=terjadwal, blue=hari ini, gray=selesai)

- **JavaScript Functions:**
  - `showEventDetails(event)` - Tampilkan detail dalam modal
  - `formatDate(date)` - Format tanggal Indonesia
  - `getStatusClass(status)` dan `getStatusLabel(status)` - Status styling
  - Auto-refresh calendar (30 menit)

#### Event Data Structure:
```javascript
FridayEvent = {
    id, title, start, allDay, backgroundColor,
    extendedProps: {
        prayer_time, imam_name, khotib_name, khutbah_theme,
        khutbah_description, location, special_notes, status
    }
}
```

### 3. admin/masjid/jadwal_jumat.php (Admin Management)

#### Fungsi Utama yang HARUS Dipertahankan:
- **CRUD Operations:**
  - CREATE: Insert new Friday schedule dengan validation
  - READ: Paginated list dengan status indicators
  - UPDATE: Edit existing schedule dengan semua fields
  - DELETE: Soft delete dengan confirmation

- **Form Management:**
  - Auto-fill next Friday date dengan JavaScript
  - Friday date validation (harus hari Jumat)
  - Datalist untuk imam, khotib, dan tema (dari database)
  - CSRF token protection

- **Database Queries:**
  - Speakers query untuk dropdown (`friday_speakers` table)
  - Themes query untuk suggestions (`khutbah_themes` table)
  - Paginated schedules dengan status calculation
  - Activity logging untuk audit trail

- **Permission System:**
  - Role-based access control
  - Permission checks untuk create/update/delete
  - User session management

- **UI Components:**
  - Sidebar navigation
  - Status badges dengan color coding
  - Responsive table dengan overflow-x-auto
  - Pagination system
  - Success/error message display

#### Form Fields:
```php
$form_fields = [
    'friday_date' => 'required|date|friday',
    'prayer_time' => 'required|time',
    'imam_name' => 'required|string',
    'khotib_name' => 'required|string', 
    'khutbah_theme' => 'required|string',
    'khutbah_description' => 'optional|text',
    'location' => 'optional|string',
    'special_notes' => 'optional|text',
    'status' => 'enum:scheduled,completed,cancelled'
]
```

## API Dependencies yang HARUS Tetap Kompatibel

### 1. api/friday_schedule_events.php
- **Input:** GET request (no parameters)
- **Output:** JSON dengan format FullCalendar events
- **Usage:** Calendar view data source

### 2. api/friday_schedule_crud.php  
- **Input:** POST dengan action (create/update/delete) + form data
- **Output:** JSON success/error response
- **Usage:** Admin CRUD operations

### 3. api/friday_schedule_ical.php
- **Input:** GET request (no parameters) 
- **Output:** iCal file download
- **Usage:** Export functionality

## Database Schema Dependencies

### friday_schedules table:
```sql
- id (PRIMARY KEY)
- friday_date (UNIQUE, DATE)
- prayer_time (TIME)
- imam_name (VARCHAR)
- khotib_name (VARCHAR)
- khutbah_theme (VARCHAR)
- khutbah_description (TEXT)
- location (VARCHAR)
- special_notes (TEXT)
- status (ENUM: scheduled, completed, cancelled)
- created_by (INT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Supporting tables:
- `friday_speakers` - Untuk dropdown imam/khotib
- `khutbah_themes` - Untuk suggestions tema
- `users` - Untuk permission system

## Styling Classes yang HARUS Dipertahankan

### Status Indicators:
- `bg-blue-50 border-l-4 border-blue-400` - Today highlight
- `bg-green-50 border-l-4 border-green-400` - Next Friday highlight
- `ring-2 ring-blue-500` - Today card highlight
- `bg-yellow-50 border-yellow-200` - Special notes

### Color Coding:
- Green (600/700) - Primary actions, scheduled status
- Blue (600/700) - Today indicators, info
- Yellow (50/200) - Warnings, special notes
- Red (600/700) - Delete actions, cancelled status
- Gray (50/300) - Neutral, completed status

## JavaScript Dependencies

### External Libraries:
- **FullCalendar v6.1.10** - Calendar functionality
- **Tailwind CSS** - Styling framework
- **Font Awesome 6.0.0** - Icons

### Custom Scripts:
- Auto-refresh functionality (30 menit)
- Modal management
- Form validation
- Date manipulation
- View toggle management

## Security Features yang HARUS Dipertahankan

1. **CSRF Protection** - Token validation untuk forms
2. **Permission Checks** - Role-based access control
3. **Input Sanitization** - htmlspecialchars untuk output
4. **SQL Injection Prevention** - Prepared statements
5. **Session Management** - User authentication
6. **Activity Logging** - Audit trail untuk changes

## Integration Points

### Settings System:
- `initializePageSettings()` - Site configuration
- `getContactInfo()` - Contact information
- `isDevelopmentMode()` - Development flags

### Authentication:
- `getCurrentUser()` - Current user data
- `hasPermission()` - Permission checking
- `requirePermission()` - Access control

### Logging:
- `logActivity()` - Activity tracking
- Error logging dengan `error_log()`

## Kesimpulan

Semua fungsi di atas HARUS dipertahankan dalam implementasi unified system untuk memastikan:
1. **Backward Compatibility** (Requirements 4.4, 6.2)
2. **Data Preservation** (Requirements 1.3, 4.3, 6.1) 
3. **Feature Completeness** (Requirements 6.4, 6.5)
4. **Security Maintenance** (Best practices)

Implementasi baru akan menggabungkan semua fungsi ini dalam satu halaman dengan toggle view dan modal popup, tanpa menghilangkan fungsionalitas yang sudah ada.