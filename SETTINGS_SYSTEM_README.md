# Dynamic Settings System

## Overview
The website now uses a comprehensive dynamic settings system that allows all website content to be managed from the admin panel. Settings are stored in the database and automatically applied across all pages.

## How It Works

### 1. Settings Loader (`includes/settings_loader.php`)
This is the core file that handles all settings loading and provides helper functions:

- `loadWebsiteSettings()` - Loads all settings from database with fallbacks
- `getWebsiteSetting($key, $default)` - Get a specific setting value
- `getSocialMediaLinks()` - Get all social media URLs
- `getContactInfo()` - Get contact information
- `getMasjidProfile()` - Get masjid profile data
- `getDonationSettings()` - Get donation configuration
- `getPrayerSettings()` - Get prayer time settings

### 2. Updated Files
The following files have been updated to use the new system:

#### Core Files:
- `includes/settings_loader.php` - New settings management system
- `partials/header.php` - Updated to use dynamic settings
- `partials/footer.php` - Updated to use dynamic settings

#### Pages:
- `index.php` - Updated to use new settings system
- `pages/profil.php` - Updated to use dynamic masjid profile
- `pages/kontak.php` - Updated to use dynamic contact info
- `pages/donasi.php` - Updated to use dynamic donation settings
- `pages/jadwal_sholat.php` - Updated to use dynamic prayer settings
- `pages/berita.php` - Updated to use new settings system
- `pages/galeri.php` - Updated to use new settings system

### 3. Admin Panel Integration
The admin panel (`admin/masjid/pengaturan.php`) already has all the necessary forms to manage these settings:

- **General Settings**: Site name, description, masjid information
- **Contact Settings**: Address, phone, email, WhatsApp
- **Social Media**: Facebook, Instagram, YouTube, Twitter, Telegram
- **Branding**: Logo upload and management
- **Prayer Settings**: API configuration and location settings
- **Donation Settings**: Account information and transparency text

## Usage in Pages

### Basic Usage
```php
<?php
require_once '../includes/settings_loader.php';

// Initialize settings for the page
$settings = initializePageSettings();

// Get specific setting
$site_name = getWebsiteSetting('site_name');
$contact_phone = getWebsiteSetting('contact_phone');
?>
```

### Using Helper Functions
```php
<?php
// Get social media links
$social_links = getSocialMediaLinks();
if (!empty($social_links['facebook'])) {
    echo '<a href="' . $social_links['facebook'] . '">Facebook</a>';
}

// Get contact information
$contact_info = getContactInfo();
echo $contact_info['phone'];
echo $contact_info['email'];

// Get WhatsApp link with message
$whatsapp_link = getWhatsAppLink('Hello from website');

// Get Google Maps link
$maps_link = getGoogleMapsLink();
?>
```

## Database Structure
Settings are stored in the `settings` table with the following structure:
- `setting_key` - Unique identifier for the setting
- `setting_value` - The actual value
- `setting_type` - Type of setting (text, textarea, image, etc.)
- `description` - Human-readable description

## Fallback System
If database is not available or a setting is not found, the system falls back to default values defined in `config/site_defaults.php`.

## Benefits

1. **Centralized Management**: All website content can be managed from one admin panel
2. **Dynamic Updates**: Changes in admin panel immediately reflect across the entire website
3. **Fallback Protection**: Website continues to work even if database is unavailable
4. **Performance**: Settings are loaded once per page and cached
5. **Maintainability**: Easy to add new settings without code changes

## Adding New Settings

### 1. Add to Admin Panel
Add form fields to `admin/masjid/pengaturan.php` in the appropriate tab.

### 2. Add to Settings Loader
Add helper functions to `includes/settings_loader.php` if needed.

### 3. Add Default Values
Add default values to `config/site_defaults.php` for fallback.

### 4. Use in Pages
Use `getWebsiteSetting()` or helper functions to access the setting.

## Testing
Use `test_settings.php` to verify that all settings are loading correctly.

## Migration Notes
- Old `getAllSiteSettings()` function is replaced with `initializePageSettings()`
- Old `getSiteSetting()` function is replaced with `getWebsiteSetting()`
- All pages now automatically load settings when including `settings_loader.php`