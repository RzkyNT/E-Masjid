# Website Setup Guide - Masjid Jami Al-Muhajirin

## Overview

This guide explains how to set up the complete website for Masjid Jami Al-Muhajirin with initial content and configuration.

## Setup Options

### Option 1: Complete Initialization (Recommended)

Run the complete initialization script that sets up everything:

```
http://your-domain/initialize_website.php
```

This script will:
- Create and configure the database
- Set up all necessary tables
- Initialize comprehensive website settings
- Create upload directory structure
- Provide navigation to next steps

### Option 2: Step-by-Step Setup

#### Step 1: Database Setup
```
http://your-domain/setup_database.php
```

#### Step 2: Add Sample Content
```
http://your-domain/seed_content.php
```

## What Gets Initialized

### Database Tables
- `users` - User authentication and roles
- `articles` - News and announcements
- `gallery` - Photo and video gallery
- `contacts` - Contact form submissions
- `settings` - Website configuration
- `prayer_schedule` - Prayer times data

### Sample Content
- **Articles**: 5 sample articles including welcome message, kajian info, activities, and announcements
- **Gallery**: 8 sample gallery items covering facilities, activities, and kajian
- **Settings**: Comprehensive website configuration including:
  - Basic site information
  - Masjid profile and history
  - Contact information
  - Social media links
  - Donation account details
  - DKM structure
  - Facilities list
  - Prayer time settings

### Directory Structure
```
assets/uploads/
├── gallery/
│   └── thumbnails/
├── articles/
└── settings/
```

## Configuration Files

### config/site_defaults.php
Contains default values and helper functions for:
- Site settings with database fallbacks
- Navigation menu items
- Masjid facilities and mission
- DKM structure
- Donation accounts
- Social media links
- Prayer time fallbacks

### Key Helper Functions
- `getSiteSetting($key, $default)` - Get setting with fallback
- `getAllSiteSettings()` - Get all settings
- `getMasjidFacilities()` - Get facilities as array
- `getDKMStructure()` - Get DKM structure
- `getDonationAccounts()` - Get donation accounts
- `getWhatsAppLink($message)` - Generate WhatsApp link
- `getGoogleMapsLink()` - Generate Google Maps link

## Default Login

After setup, use these credentials to access the admin panel:

- **Username**: `admin`
- **Password**: `password`

**⚠️ Important**: Change the default password immediately after first login.

## Admin Access

After setup, access the admin panel at:
- Login: `http://your-domain/admin/login.php`
- Dashboard: `http://your-domain/admin/masjid/dashboard.php`

## Website Sections

The initialized website includes:

### Public Pages
- **Homepage** (`index.php`) - Hero section, prayer times, announcements
- **Profile** (`pages/profil.php`) - Masjid history, vision, mission, DKM structure
- **Prayer Schedule** (`pages/jadwal_sholat.php`) - Daily and monthly prayer times
- **News** (`pages/berita.php`) - Articles and announcements
- **Gallery** (`pages/galeri.php`) - Photos and videos
- **Donation** (`pages/donasi.php`) - Donation information and accounts
- **Contact** (`pages/kontak.php`) - Contact form and location

### Admin Pages
- **Dashboard** (`admin/masjid/dashboard.php`) - Overview and statistics
- **News Management** (`admin/masjid/berita.php`) - CRUD for articles
- **Gallery Management** (`admin/masjid/galeri.php`) - Photo/video management

## Customization

### Updating Settings
1. Login to admin panel
2. Navigate to settings management (when implemented)
3. Or directly update the `settings` table in database

### Adding Content
1. Use the admin panel to add articles and gallery items
2. Or use the sample content as templates for bulk import

### Modifying Defaults
Edit `config/site_defaults.php` to change default values and add new helper functions.

## File Structure

```
/
├── initialize_website.php     # Complete setup script
├── setup_database.php         # Database setup only
├── seed_content.php          # Sample content seeding
├── config/
│   ├── config.php            # Database configuration
│   └── site_defaults.php     # Default settings and helpers
├── database/
│   └── masjid_bimbel.sql     # Database schema
├── assets/uploads/           # Media upload directories
├── pages/                    # Public website pages
├── admin/masjid/            # Admin management pages
└── partials/                # Shared layout components
```

## Troubleshooting

### Database Connection Issues
1. Check database credentials in `config/config.php`
2. Ensure MySQL server is running
3. Verify database permissions

### File Permission Issues
1. Ensure upload directories are writable (755 permissions)
2. Check PHP file permissions

### Missing Content
1. Run `seed_content.php` to add sample content
2. Check database for existing data

## Next Steps

After successful setup:

1. **Change default password**
2. **Customize website settings** through admin panel
3. **Add real content** (replace sample content)
4. **Upload actual images** for gallery and articles
5. **Configure prayer time API** settings
6. **Set up social media links**
7. **Test all functionality**

## Support

For technical support or questions about the setup process, refer to the project documentation or contact the development team.