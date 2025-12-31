# Bimbel Database Setup Documentation

## Overview

This document describes the complete database setup for the Sistem Manajemen Bimbel Al-Muhajirin. The database is designed to support multi-level education management (SD, SMP, SMA) with comprehensive student, mentor, attendance, and financial tracking.

## Database Structure

### Core Tables

#### 1. Users Table
- **Purpose**: Authentication and role-based access control
- **Key Fields**: username, password, full_name, role, status
- **Roles**: admin_masjid, admin_bimbel, viewer
- **Security**: Passwords are hashed using PHP's password_hash()

#### 2. Students Table
- **Purpose**: Student management and registration
- **Key Fields**: student_number, full_name, level, class, parent_name, parent_phone, monthly_fee
- **Levels**: SD (1-6), SMP (7-9), SMA (10-12)
- **Auto-generation**: Student numbers with configurable prefix (default: ALM)

#### 3. Mentors Table
- **Purpose**: Mentor/teacher management
- **Key Fields**: mentor_code, full_name, teaching_levels (JSON), hourly_rate
- **Features**: Multi-level teaching support, rate history tracking
- **Auto-generation**: Mentor codes with configurable prefix (default: MNT)

#### 4. Student Attendance Table
- **Purpose**: Daily student attendance tracking
- **Key Fields**: student_id, attendance_date, status, notes
- **Status Options**: present, absent, sick, permission
- **Constraints**: Unique per student per date

#### 5. Mentor Attendance Table
- **Purpose**: Mentor attendance and hours tracking
- **Key Fields**: mentor_id, attendance_date, level, status, hours_taught
- **Features**: Level-specific attendance, payment calculation basis
- **Constraints**: Unique per mentor per date per level

#### 6. SPP Payments Table
- **Purpose**: Monthly student fee payments
- **Key Fields**: student_id, payment_month, payment_year, amount, payment_date
- **Features**: Payment method tracking, duplicate prevention
- **Constraints**: Unique per student per month/year

#### 7. Financial Transactions Table
- **Purpose**: Complete financial transaction logging
- **Key Fields**: transaction_type, category, amount, description, transaction_date
- **Types**: income, expense
- **Categories**: spp, registration, operational, mentor_payment, utilities, other

#### 8. Monthly Recap Table
- **Purpose**: Automated monthly financial summaries
- **Key Fields**: recap_month, recap_year, opening_balance, total_income, total_expense
- **Features**: Automated calculation, audit trail
- **Constraints**: Unique per month/year

### Database Views

#### 1. v_students_payment_status
- **Purpose**: Real-time student payment status
- **Fields**: student info + payment_status (Lunas/Belum Bayar/Tunggakan)
- **Usage**: Dashboard, payment monitoring

#### 2. v_mentor_performance
- **Purpose**: Mentor performance analytics
- **Fields**: mentor info + attendance_rate, total_hours, total_earnings
- **Usage**: Performance reports, payment calculation

### Performance Optimizations

#### Indexes Created
- `idx_students_level_status` - Fast filtering by level and status
- `idx_students_registration_date` - Date-based queries
- `idx_student_attendance_date_status` - Attendance reporting
- `idx_mentor_attendance_date_level` - Mentor attendance queries
- `idx_spp_payments_month_year` - Payment period queries
- `idx_financial_transactions_date_type` - Financial reporting

#### Query Optimization
- Proper foreign key relationships for data integrity
- Composite indexes for common query patterns
- JSON validation for mentor teaching_levels
- Efficient date range queries

## Configuration Settings

### Bimbel-Specific Settings
```
bimbel_name: Bimbel Al-Muhajirin
academic_year: 2024/2025
semester_active: 1

Fee Structure:
- fee_sd: 200,000 IDR
- fee_smp: 300,000 IDR  
- fee_sma: 400,000 IDR
- registration_fee: 50,000 IDR

Mentor Rates:
- mentor_rate_sd: 75,000 IDR per session
- mentor_rate_smp: 100,000 IDR per session
- mentor_rate_sma: 125,000 IDR per session

System Settings:
- max_students_per_class: 10
- class_duration_minutes: 120
- attendance_minimum_percentage: 75%
- late_payment_penalty: 10,000 IDR
```

### Auto-Generation Settings
```
student_number_prefix: ALM
mentor_code_prefix: MNT
auto_generate_student_number: enabled
auto_generate_mentor_code: enabled
```

## Sample Data

### Users (3 records)
- admin (admin_masjid) - Full system access
- admin_bimbel (admin_bimbel) - Bimbel management access
- viewer_bimbel (viewer) - Read-only access

### Students (18 records)
- 6 SD students (classes 1-6)
- 6 SMP students (classes 7-9)  
- 6 SMA students (classes 10-12)
- All with complete parent information and contact details

### Mentors (8 records)
- Multi-level teaching capabilities
- Varied hourly rates based on experience
- Valid JSON teaching_levels configuration
- Complete contact and qualification information

### Attendance Data
- 270 student attendance records (15 days × 18 students)
- 154 mentor attendance records (level-specific)
- Realistic attendance patterns (85% present rate)
- Proper date distribution

### Financial Data
- 16 SPP payment records (previous month)
- 10 financial transaction records
- Income and expense categorization
- Proper audit trail with recorded_by references

## Setup Instructions

### 1. Database Creation
```bash
# Run the main setup script
php setup_bimbel_database.php
```

### 2. Verification
```bash
# Verify the setup
php verify_bimbel_setup.php
```

### 3. Manual Setup (if needed)
```sql
-- Create database
CREATE DATABASE masjid_bimbel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import main schema
SOURCE database/masjid_bimbel.sql;

-- Import bimbel-specific data
SOURCE database/bimbel_setup.sql;
```

## Security Considerations

### Access Control
- Role-based permissions (admin_masjid, admin_bimbel, viewer)
- Password hashing with PHP's password_hash()
- Session management with timeout
- SQL injection prevention with prepared statements

### Data Integrity
- Foreign key constraints
- Unique constraints on critical fields
- JSON validation for structured data
- Date and amount validation

### Audit Trail
- All financial transactions logged with user reference
- Attendance records with recorded_by tracking
- Timestamp tracking on all critical operations
- Monthly recap generation for accountability

## Business Rules Implemented

### Student Management
1. Unique student numbers per academic year
2. Level-appropriate class assignments
3. Parent contact information required
4. Status tracking (active/inactive/graduated)

### Mentor Management
1. Multi-level teaching capability
2. Rate history tracking
3. Teaching level validation against JSON schema
4. Performance metrics calculation

### Attendance Rules
1. One attendance record per student per date
2. One attendance record per mentor per date per level
3. Status validation (present/absent/sick/permission)
4. Hours taught tracking for payment calculation

### Financial Rules
1. One SPP payment per student per month
2. All transactions require category and description
3. Monthly recap prevents duplicate generation
4. Audit trail for all financial operations

## Maintenance Tasks

### Daily
- Backup attendance data
- Monitor payment status
- Check system performance

### Weekly  
- Review attendance rates
- Generate mentor performance reports
- Validate data integrity

### Monthly
- Generate financial recap
- Archive old attendance data
- Update fee structures if needed
- Performance optimization review

## Troubleshooting

### Common Issues

#### Database Connection
```php
// Check config/config.php for correct credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'masjid_bimbel');
define('DB_USER', 'root');
define('DB_PASS', '');
```

#### Missing Tables
```bash
# Re-run setup script
php setup_bimbel_database.php
```

#### Performance Issues
```sql
-- Check index usage
EXPLAIN SELECT * FROM students WHERE level = 'SD' AND status = 'active';

-- Rebuild indexes if needed
ANALYZE TABLE students;
```

#### Data Validation Errors
```sql
-- Check for invalid JSON in teaching_levels
SELECT * FROM mentors WHERE NOT JSON_VALID(teaching_levels);

-- Check for duplicate student numbers
SELECT student_number, COUNT(*) FROM students GROUP BY student_number HAVING COUNT(*) > 1;
```

## Future Enhancements

### Planned Features
1. Automated report generation
2. SMS/WhatsApp integration for notifications
3. Online payment integration
4. Mobile app support
5. Advanced analytics dashboard

### Database Optimizations
1. Partitioning for large attendance tables
2. Read replicas for reporting
3. Caching layer for frequent queries
4. Archive strategy for historical data

## Support

For technical support or questions about the database setup:

1. Check the verification script output
2. Review error logs in the application
3. Consult this documentation
4. Contact the development team

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Database Schema Version**: 1.0