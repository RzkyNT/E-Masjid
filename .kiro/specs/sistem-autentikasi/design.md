# Design Document - Sistem Autentikasi & Hak Akses

## Overview

Sistem Autentikasi & Hak Akses dirancang sebagai fondasi keamanan untuk Sistem Informasi Terpadu Masjid Jami Al-Muhajirin. Sistem ini menggunakan session-based authentication dengan PHP native dan MySQL, mendukung tiga tingkat hak akses yang berbeda, serta memisahkan akses publik dan admin.

## Architecture

### High-Level Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Public Web    │    │   Admin Panel    │    │    Database     │
│   (No Auth)     │    │  (Auth Required) │    │     MySQL       │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌──────────────────┐
                    │ Authentication   │
                    │    System        │
                    │ (Session-based)  │
                    └──────────────────┘
```

### Session Management Flow

```
Login Request → Validate Credentials → Create Session → Set Role → Redirect to Dashboard
     ↓
Session Check ← Every Admin Page Access
     ↓
Valid Session? → Yes: Allow Access | No: Redirect to Login
```

## Components and Interfaces

### 1. Authentication Core (`config/auth.php`)

**Responsibilities:**
- Session management
- Password hashing and verification
- Role-based access control
- Security utilities

**Key Functions:**
```php
// Core authentication functions
function login($username, $password)
function logout()
function isLoggedIn()
function getCurrentUser()
function hasRole($required_role)
function checkSession()
```

### 2. Database Configuration (`config/config.php`)

**Responsibilities:**
- Database connection management
- Security configurations
- Session settings

### 3. Login Interface (`admin/login.php`)

**Features:**
- Clean login form with CSRF protection
- Input validation and sanitization
- Error message display
- Redirect handling

### 4. Access Control Middleware

**Implementation:**
- Session validation on each admin page
- Role-based page access control
- Automatic logout on session expiry

### 5. User Management Interface (`admin/users.php`)

**Features:**
- CRUD operations for user accounts
- Password management
- Role assignment
- User status management

## Data Models

### Users Table Structure

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin_masjid', 'admin_bimbel', 'viewer') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Sessions Management

**PHP Session Configuration:**
- Session timeout: 30 minutes
- Secure session cookies
- Session regeneration on login
- HttpOnly cookies for security

## Role-Based Access Control

### Access Matrix

| Resource | Admin Masjid | Admin Bimbel | Viewer |
|----------|--------------|--------------|--------|
| Dashboard Masjid | Full Access | No Access | Read Only |
| Dashboard Bimbel | Read Only | Full Access | Read Only |
| Manajemen Siswa | Read Only | Full Access | No Access |
| Manajemen Mentor | Read Only | Full Access | No Access |
| Keuangan Bimbel | Read Only | Full Access | Read Only |
| Laporan | Full Access | Full Access | Read Only |
| User Management | Full Access | No Access | No Access |

### Implementation Strategy

```php
// Role checking function
function checkAccess($required_role, $resource = null) {
    $user_role = getCurrentUser()['role'];
    
    switch($resource) {
        case 'bimbel_crud':
            return $user_role === 'admin_bimbel';
        case 'user_management':
            return $user_role === 'admin_masjid';
        case 'reports':
            return in_array($user_role, ['admin_masjid', 'admin_bimbel', 'viewer']);
        default:
            return isLoggedIn();
    }
}
```

## Security Measures

### 1. Password Security
- Password hashing using `password_hash()` with PASSWORD_DEFAULT
- Minimum password requirements (8 characters, mixed case, numbers)
- Password verification using `password_verify()`

### 2. Session Security
- Session regeneration on login
- HttpOnly and Secure cookie flags
- Session timeout implementation
- CSRF token protection

### 3. Input Validation
- SQL injection prevention using prepared statements
- XSS protection with `htmlspecialchars()`
- Input sanitization and validation

### 4. Access Control
- Page-level access control
- Function-level permission checks
- Automatic logout on unauthorized access attempts

## Error Handling

### Authentication Errors
- Invalid credentials: Clear error message without revealing which field is wrong
- Account locked: Informative message with contact information
- Session expired: Automatic redirect to login with notification

### Access Denied Scenarios
- Insufficient permissions: Redirect to appropriate dashboard with message
- Unauthorized page access: Redirect to login
- Invalid session: Clear session and redirect to login

## Testing Strategy

### Unit Testing Focus
- Password hashing and verification
- Session management functions
- Role validation logic
- Input sanitization functions

### Integration Testing
- Login/logout flow
- Role-based access control
- Session timeout behavior
- CSRF protection

### Security Testing
- SQL injection attempts
- XSS attack prevention
- Session hijacking protection
- Brute force login protection

## File Structure

```
/masjid/
├── config/
│   ├── config.php          # Database & app configuration
│   └── auth.php            # Authentication functions
├── admin/
│   ├── login.php           # Login interface
│   ├── logout.php          # Logout handler
│   ├── dashboard.php       # Main dashboard
│   └── users.php           # User management (Admin Masjid only)
├── includes/
│   ├── session_check.php   # Session validation middleware
│   └── access_control.php  # Role-based access functions
└── assets/
    ├── css/
    │   └── auth.css        # Authentication styling
    └── js/
        └── auth.js         # Client-side validation
```

## Implementation Considerations

### Hosting Compatibility
- Compatible with shared hosting (InfinityFree)
- No external dependencies beyond PHP and MySQL
- Minimal server resource usage

### Scalability
- Efficient session management
- Optimized database queries
- Modular code structure for easy extension

### Maintenance
- Clear separation of concerns
- Well-documented functions
- Easy configuration management
- Comprehensive error logging