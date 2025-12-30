# Design Document - Sistem Manajemen Bimbel

## Overview

Sistem Manajemen Bimbel Al-Muhajirin dirancang sebagai modul internal yang terintegrasi dengan sistem autentikasi masjid untuk mengelola operasional bimbingan belajar. Sistem ini menggunakan PHP native dengan MySQL, mendukung multi-jenjang (SD, SMP, SMA), dan menyediakan role-based access control untuk Admin Bimbel, Admin Masjid, dan Viewer.

## Architecture

### High-Level Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Admin Bimbel  │    │   Admin Masjid   │    │     Viewer      │
│  (Full Access)  │    │  (Read Only)     │    │  (Summary Only) │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌──────────────────┐
                    │ Bimbel Management│
                    │     System       │
                    └──────────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         │                       │                       │
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Student Mgmt  │    │   Financial Mgmt │    │   Report System │
│   - Students    │    │   - SPP Payment  │    │   - Monthly     │
│   - Mentors     │    │   - Expenses     │    │   - Financial   │
│   - Attendance  │    │   - Mentor Pay   │    │   - Attendance  │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

### Data Flow Architecture

```
Input Data → Validation → Database → Business Logic → Reports
     ↓
Student/Mentor Registration → Attendance Recording → Payment Processing → Monthly Recap
```

## Components and Interfaces

### 1. Dashboard System (`admin/bimbel/dashboard.php`)

**Features:**
- Role-based dashboard content
- Key performance indicators (KPIs)
- Quick access to common functions
- Recent activity summary

**KPIs Display:**
- Total students by level (SD/SMP/SMA)
- Active mentors count
- Monthly revenue and expenses
- Attendance rates

### 2. Student Management (`admin/bimbel/siswa.php`)

**Core Functions:**
```php
// Student management functions
function addStudent($data)
function updateStudent($id, $data)
function getStudentsByLevel($level)
function getStudentsByStatus($status)
function searchStudents($keyword)
```

**Features:**
- Student registration with complete data
- Level and class assignment
- Status management (active/inactive/graduated)
- Student search and filtering

### 3. Mentor Management (`admin/bimbel/mentor.php`)

**Core Functions:**
```php
// Mentor management functions
function addMentor($data)
function updateMentor($id, $data)
function getMentorsByLevel($level)
function updateMentorRate($id, $rate)
function getMentorPaymentHistory($id)
```

**Features:**
- Mentor registration and profile management
- Teaching level assignment
- Hourly rate management with history
- Performance tracking

### 4. Attendance System

**Student Attendance (`admin/bimbel/absensi_siswa.php`)**
```php
// Student attendance functions
function recordStudentAttendance($date, $class_id, $attendance_data)
function getStudentAttendance($student_id, $month, $year)
function getClassAttendanceByDate($class_id, $date)
function calculateAttendanceRate($student_id, $period)
```

**Mentor Attendance (`admin/bimbel/absensi_mentor.php`)**
```php
// Mentor attendance functions
function recordMentorAttendance($date, $mentor_id, $level, $status)
function getMentorAttendance($mentor_id, $month, $year)
function calculateMentorPayment($mentor_id, $month, $year)
function getMentorAttendanceByDate($date)
```

### 5. Financial Management (`admin/bimbel/keuangan.php`)

**Core Functions:**
```php
// Financial management functions
function recordSPPPayment($student_id, $month, $year, $amount)
function recordExpense($category, $amount, $description)
function getMonthlyRevenue($month, $year)
function getMonthlyExpenses($month, $year)
function generateMonthlyRecap($month, $year)
```

**Categories:**
- Revenue: SPP, Registration fees
- Expenses: Operational costs, Mentor payments, Utilities

### 6. Reporting System (`admin/bimbel/laporan.php`)

**Report Types:**
- Monthly financial recap
- Student attendance reports
- Mentor payment reports
- Revenue and expense analysis
- Student performance summaries

## Data Models

### Students Table

```sql
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    level ENUM('SD', 'SMP', 'SMA') NOT NULL,
    class VARCHAR(10) NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    parent_phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    registration_date DATE NOT NULL,
    monthly_fee DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Mentors Table

```sql
CREATE TABLE mentors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT NOT NULL,
    teaching_levels JSON NOT NULL, -- ['SD', 'SMP'] etc
    hourly_rate DECIMAL(10,2) NOT NULL,
    join_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Student Attendance Table

```sql
CREATE TABLE student_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'sick', 'permission') NOT NULL,
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    UNIQUE KEY unique_student_date (student_id, attendance_date)
);
```

### Mentor Attendance Table

```sql
CREATE TABLE mentor_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentor_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    level ENUM('SD', 'SMP', 'SMA') NOT NULL,
    status ENUM('present', 'absent', 'sick', 'permission') NOT NULL,
    hours_taught DECIMAL(4,2) DEFAULT 0,
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES mentors(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    UNIQUE KEY unique_mentor_date_level (mentor_id, attendance_date, level)
);
```

### SPP Payments Table

```sql
CREATE TABLE spp_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    payment_month INT NOT NULL, -- 1-12
    payment_year INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'transfer', 'other') DEFAULT 'cash',
    notes TEXT,
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    UNIQUE KEY unique_student_month_year (student_id, payment_month, payment_year)
);
```

### Financial Transactions Table

```sql
CREATE TABLE financial_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_type ENUM('income', 'expense') NOT NULL,
    category ENUM('spp', 'registration', 'operational', 'mentor_payment', 'utilities', 'other') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    transaction_date DATE NOT NULL,
    reference_id INT, -- Link to related record (student_id, mentor_id, etc)
    recorded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);
```

### Monthly Recap Table

```sql
CREATE TABLE monthly_recap (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recap_month INT NOT NULL,
    recap_year INT NOT NULL,
    opening_balance DECIMAL(12,2) NOT NULL,
    total_income DECIMAL(12,2) NOT NULL,
    total_expense DECIMAL(12,2) NOT NULL,
    closing_balance DECIMAL(12,2) NOT NULL,
    spp_income DECIMAL(12,2) NOT NULL,
    registration_income DECIMAL(12,2) NOT NULL,
    mentor_payment_expense DECIMAL(12,2) NOT NULL,
    operational_expense DECIMAL(12,2) NOT NULL,
    total_students INT NOT NULL,
    total_mentors INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    generated_by INT NOT NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id),
    UNIQUE KEY unique_month_year (recap_month, recap_year)
);
```

## Business Logic Implementation

### 1. Attendance Management

**Student Attendance Logic:**
```php
class StudentAttendanceManager {
    public function recordDailyAttendance($date, $class, $attendance_data) {
        // Validate date and class
        // Record attendance for each student
        // Update attendance statistics
    }
    
    public function calculateMonthlyAttendanceRate($student_id, $month, $year) {
        // Count present days vs total school days
        // Return percentage
    }
}
```

**Mentor Attendance Logic:**
```php
class MentorAttendanceManager {
    public function recordMentorAttendance($mentor_id, $date, $level, $hours) {
        // Validate mentor can teach the level
        // Record attendance with hours taught
        // Calculate payment based on hourly rate
    }
    
    public function calculateMonthlyPayment($mentor_id, $month, $year) {
        // Sum hours taught * hourly rate
        // Apply any bonuses or deductions
        // Return total payment due
    }
}
```

### 2. Financial Management

**SPP Payment Processing:**
```php
class SPPManager {
    public function recordPayment($student_id, $month, $year, $amount) {
        // Validate student and payment amount
        // Record payment in spp_payments table
        // Create financial transaction record
        // Update student payment status
    }
    
    public function getOutstandingPayments() {
        // Find students with unpaid SPP
        // Calculate total outstanding amount
        // Return list with payment details
    }
}
```

**Monthly Recap Generation:**
```php
class MonthlyRecapGenerator {
    public function generateRecap($month, $year) {
        // Calculate opening balance from previous month
        // Sum all income (SPP, registration)
        // Sum all expenses (mentor payments, operational)
        // Calculate closing balance
        // Store in monthly_recap table
    }
}
```

## Role-Based Access Control

### Access Matrix

| Feature | Admin Bimbel | Admin Masjid | Viewer |
|---------|--------------|--------------|--------|
| Student Management | Full CRUD | Read Only | No Access |
| Mentor Management | Full CRUD | Read Only | No Access |
| Attendance Recording | Full Access | No Access | No Access |
| SPP Management | Full Access | Read Only | No Access |
| Financial Transactions | Full Access | Read Only | Summary Only |
| Reports Generation | Full Access | Full Access | Summary Only |
| Monthly Recap | Full Access | Read Only | Read Only |

### Implementation

```php
function checkBimbelAccess($action, $resource) {
    $user_role = getCurrentUser()['role'];
    
    switch($action) {
        case 'create':
        case 'update':
        case 'delete':
            return $user_role === 'admin_bimbel';
        
        case 'read':
            if ($resource === 'financial_detail') {
                return in_array($user_role, ['admin_bimbel', 'admin_masjid']);
            }
            return in_array($user_role, ['admin_bimbel', 'admin_masjid', 'viewer']);
        
        default:
            return false;
    }
}
```

## User Interface Design

### 1. Dashboard Layout

```
┌─────────────────────────────────────────┐
│              Bimbel Dashboard            │
├─────────────────────────────────────────┤
│  Students: 150 | Mentors: 8 | Revenue   │
│  SD: 60 SMP: 50 SMA: 40    | This Month │
├─────────────────────────────────────────┤
│  Quick Actions                          │
│  [Record Attendance] [Add Payment]      │
│  [Add Student] [Generate Report]        │
├─────────────────────────────────────────┤
│  Recent Activities                      │
│  - Payment received from Ahmad (SD)     │
│  - New student registered: Siti (SMP)   │
│  - Monthly recap generated for Nov      │
└─────────────────────────────────────────┘
```

### 2. Navigation Structure

```
Bimbel Dashboard
├── Manajemen Siswa
│   ├── Daftar Siswa
│   ├── Tambah Siswa
│   └── Status Pembayaran
├── Manajemen Mentor
│   ├── Daftar Mentor
│   ├── Tambah Mentor
│   └── Perhitungan Honor
├── Absensi
│   ├── Absensi Siswa
│   └── Absensi Mentor
├── Keuangan
│   ├── Pembayaran SPP
│   ├── Transaksi Keuangan
│   └── Rekap Bulanan
└── Laporan
    ├── Laporan Keuangan
    ├── Laporan Kehadiran
    └── Laporan Performa
```

## Performance Considerations

### 1. Database Optimization
- Proper indexing on frequently queried columns
- Efficient queries for attendance and payment reports
- Pagination for large data sets
- Caching for monthly recaps

### 2. User Experience
- Fast loading dashboard with key metrics
- Bulk operations for attendance recording
- Auto-save functionality for forms
- Responsive design for mobile access

## Security Measures

### 1. Data Protection
- Input validation and sanitization
- SQL injection prevention
- Role-based data access
- Audit trail for financial transactions

### 2. Business Logic Security
- Payment validation and verification
- Attendance duplicate prevention
- Financial transaction integrity
- Monthly recap validation

## Integration Points

### 1. Authentication System
- Seamless integration with masjid authentication
- Role-based access control
- Session management consistency

### 2. Reporting System
- Export capabilities (PDF, Excel)
- Email notifications for important events
- Dashboard widgets for quick overview

## File Structure

```
/masjid/admin/bimbel/
├── dashboard.php           # Main dashboard
├── siswa.php              # Student management
├── mentor.php             # Mentor management
├── absensi_siswa.php      # Student attendance
├── absensi_mentor.php     # Mentor attendance
├── spp.php                # SPP payment management
├── keuangan.php           # Financial management
├── laporan.php            # Reports and analytics
└── includes/
    ├── bimbel_functions.php    # Core business logic
    ├── attendance_handler.php  # Attendance processing
    ├── payment_handler.php     # Payment processing
    └── report_generator.php    # Report generation
```