# Design Document - Website Masjid Publik

## Overview

Website Masjid Publik dirancang sebagai portal informasi yang dapat diakses oleh jamaah umum tanpa autentikasi. Website ini menggunakan PHP native dengan MySQL sebagai database, Tailwind CSS untuk styling, dan dirancang responsif untuk berbagai perangkat. Sistem ini terintegrasi dengan sistem autentikasi untuk pengelolaan konten oleh admin.

## Architecture

### High-Level Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Public Pages  │    │   Admin CMS      │    │    Database     │
│   (No Auth)     │    │  (Auth Required) │    │     MySQL       │
│                 │    │                  │    │                 │
│ - Beranda       │    │ - Kelola Berita  │    │ - articles      │
│ - Profil        │    │ - Kelola Galeri  │    │ - gallery       │
│ - Jadwal Sholat │    │ - Kelola Konten  │    │ - contacts      │
│ - Berita        │    │ - Pengaturan     │    │ - settings      │
│ - Galeri        │    │                  │    │                 │
│ - Donasi        │    │                  │    │                 │
│ - Kontak        │    │                  │    │                 │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                    ┌──────────────────┐
                    │   Shared Layout  │
                    │ (Header/Footer)  │
                    └──────────────────┘
```

### Content Management Flow

```
Admin Login → Content Management → Database Update → Public Display
     ↓
Content CRUD ← Admin Panel ← Authentication Check
     ↓
Public Cache Update ← Database Changes
```

## Components and Interfaces

### 1. Public Website Structure

**Main Layout (`partials/header.php`, `partials/footer.php`)**
- Responsive navigation menu
- Masjid branding and logo
- Contact information in footer
- Social media links

**Page Components:**
```php
// Core public pages
pages/index.php          // Beranda
pages/profil.php         // Profil Masjid
pages/jadwal_sholat.php  // Jadwal Sholat
pages/berita.php         // Berita & Kegiatan
pages/galeri.php         // Galeri Foto/Video
pages/donasi.php         // Donasi & Infaq
pages/kontak.php         // Kontak & Lokasi
```

### 2. Content Management System

**Admin Interface (`admin/masjid/`)**
```php
admin/masjid/dashboard.php    // Dashboard overview
admin/masjid/berita.php       // Manajemen berita
admin/masjid/galeri.php       // Manajemen galeri
admin/masjid/konten.php       // Manajemen konten statis
admin/masjid/pengaturan.php   // Pengaturan website
```

### 3. API Integration

**Jadwal Sholat API Integration**
- Primary: External prayer time API
- Fallback: Manual schedule in database
- Caching mechanism for performance

### 4. Responsive Design System

**Tailwind CSS Implementation**
- Mobile-first approach
- Consistent color scheme
- Typography system
- Component-based styling

## Data Models

### Articles Table (Berita)

```sql
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    category ENUM('kajian', 'pengumuman', 'kegiatan') NOT NULL,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    author_id INT,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);
```

### Gallery Table

```sql
CREATE TABLE gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video') NOT NULL,
    category ENUM('kegiatan', 'fasilitas', 'kajian') NOT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Contacts Table

```sql
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Settings Table

```sql
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'image', 'json') DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Prayer Schedule Table

```sql
CREATE TABLE prayer_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    fajr TIME NOT NULL,
    sunrise TIME NOT NULL,
    dhuhr TIME NOT NULL,
    asr TIME NOT NULL,
    maghrib TIME NOT NULL,
    isha TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date)
);
```

## User Interface Design

### 1. Homepage Layout

```
┌─────────────────────────────────────────┐
│              Header & Navigation         │
├─────────────────────────────────────────┤
│              Hero Section               │
│         (Masjid Image + Welcome)        │
├─────────────────────────────────────────┤
│  Jadwal Sholat Hari Ini | Pengumuman   │
├─────────────────────────────────────────┤
│           Highlight Fasilitas           │
├─────────────────────────────────────────┤
│              Footer                     │
└─────────────────────────────────────────┘
```

### 2. Responsive Breakpoints

- **Mobile**: < 768px (Stack layout)
- **Tablet**: 768px - 1024px (2-column layout)
- **Desktop**: > 1024px (3-column layout)

### 3. Navigation Structure

```
Home | Profil | Jadwal Sholat | Berita | Galeri | Donasi | Kontak
```

## Content Management Features

### 1. Article Management
- Rich text editor for content creation
- Image upload and management
- SEO-friendly URL slugs
- Category and status management
- Publication scheduling

### 2. Gallery Management
- Bulk image upload
- Image resizing and optimization
- Category organization
- Drag-and-drop sorting
- Video embed support

### 3. Settings Management
- Website configuration
- Contact information
- Social media links
- Prayer time manual override
- Donation account information

## Performance Optimization

### 1. Caching Strategy
- Static content caching
- Database query optimization
- Image optimization and lazy loading
- Minified CSS/JS assets

### 2. SEO Optimization
- Semantic HTML structure
- Meta tags and Open Graph
- Sitemap generation
- Clean URL structure
- Fast loading times

## Security Considerations

### 1. Public Access Security
- Input sanitization for contact forms
- CSRF protection on forms
- File upload validation
- XSS prevention

### 2. Admin Content Security
- Authentication required for content management
- Role-based access to CMS features
- Secure file upload handling
- Content validation and sanitization

## Integration Points

### 1. Authentication System Integration
- Seamless login/logout for admin users
- Role-based CMS access
- Session management consistency

### 2. External API Integration
- Prayer time API with fallback
- Social media integration
- Google Maps integration for location

## File Structure

```
/masjid/
├── index.php                    # Homepage
├── pages/
│   ├── profil.php              # Profil masjid
│   ├── jadwal_sholat.php       # Jadwal sholat
│   ├── berita.php              # Daftar berita
│   ├── berita_detail.php       # Detail berita
│   ├── galeri.php              # Galeri foto/video
│   ├── donasi.php              # Informasi donasi
│   └── kontak.php              # Kontak & form
├── admin/masjid/
│   ├── dashboard.php           # Dashboard admin
│   ├── berita.php              # Kelola berita
│   ├── galeri.php              # Kelola galeri
│   ├── konten.php              # Kelola konten
│   └── pengaturan.php          # Pengaturan
├── includes/
│   ├── functions.php           # Helper functions
│   ├── prayer_api.php          # Prayer time API
│   └── upload_handler.php      # File upload
└── assets/
    ├── css/
    │   └── public.css          # Public styling
    ├── js/
    │   └── public.js           # Public JavaScript
    └── uploads/
        ├── articles/           # Article images
        └── gallery/            # Gallery files
```

## Implementation Considerations

### 1. Hosting Compatibility
- Optimized for shared hosting
- Minimal server requirements
- Efficient resource usage
- Compatible with InfinityFree limitations

### 2. Maintenance & Updates
- Easy content management interface
- Automated backup considerations
- Update-friendly code structure
- Clear documentation for admins

### 3. Scalability
- Efficient database queries
- Image optimization pipeline
- Caching mechanisms
- Modular code architecture