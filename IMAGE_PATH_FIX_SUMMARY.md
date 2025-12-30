# Image Path Inconsistency Fix

## Problem Description
User reported inconsistent image paths when uploading featured images for news articles:
- **Database stores**: `assets/uploads/articles/featured_image_1767106438.png`
- **File actually saved at**: `C:\laragon\www\test\LMS\bimbel\admin\masjid\assets\uploads\articles\featured_image_1767106438.png`
- **Browser tries to load from**: `http://localhost/test/lms/bimbel/assets/uploads/articles/featured_image_1767106438.png`
- **Result**: 404 Not Found

## Root Cause Analysis
The main issue was that the upload handler was saving files relative to the current working directory instead of the project root:

1. **Upload Handler Called From**: `admin/masjid/berita.php`
2. **Working Directory**: `admin/masjid/`
3. **File Saved To**: `admin/masjid/assets/uploads/articles/` (WRONG)
4. **Should Save To**: `assets/uploads/articles/` (from project root)

## Solution Implemented

### 1. Fixed Upload Handler (`includes/upload_handler.php`)
- Added `findProjectRoot()` method to locate project root directory
- Modified constructor to always save files relative to project root
- Enhanced path resolution to work from any calling directory

### 2. Created Image Path Helper (`includes/image_path_helper.php`)
- `getImagePath($stored_path, $from_location)`: Returns correct relative path based on current location
- `imageExists($stored_path)`: Checks if image file exists using normalized path
- `getImageUrl($stored_path, $base_url)`: Generates absolute URLs for meta tags, emails, etc.

### 3. Created File Migration Script (`fix_image_locations.php`)
- Automatically moves files from wrong locations to correct location
- Updates database paths if needed
- Provides detailed report of moved files

### 4. Enhanced Debug Tool (`debug_image_paths.php`)
- Shows actual file locations vs expected locations
- Helps identify files in wrong directories
- Provides comprehensive directory structure analysis

### 5. Updated All Display Code
- **Admin Panel**: Uses `getImagePath($path, 'admin')`
- **Public Pages**: Uses `getImagePath($path, 'public')`
- **File Checks**: Uses `imageExists($path)`

## Files Modified
- `includes/upload_handler.php` (FIXED - main issue)
- `includes/image_path_helper.php` (NEW)
- `admin/masjid/berita.php` (UPDATED)
- `pages/berita.php` (UPDATED)
- `pages/berita_detail.php` (UPDATED)
- `fix_image_locations.php` (NEW - migration tool)
- `debug_image_paths.php` (ENHANCED)

## Step-by-Step Fix Process

### Step 1: Run Debug Script
```
http://localhost/test/lms/bimbel/debug_image_paths.php
```
This will show you where files are actually located vs where they should be.

### Step 2: Run Migration Script
```
http://localhost/test/lms/bimbel/fix_image_locations.php
```
This will:
- Move files from wrong locations to correct location
- Update database paths if necessary
- Show detailed report

### Step 3: Test New Uploads
- Upload a new image from admin panel
- Verify it's saved in correct location: `assets/uploads/articles/`
- Check that it displays correctly on public pages

## Technical Details

### Upload Handler Fix
```php
private function findProjectRoot() {
    $current_dir = __DIR__;
    
    // Look for config directory to identify project root
    while ($current_dir !== dirname($current_dir)) {
        if (is_dir($current_dir . '/config') && file_exists($current_dir . '/config/config.php')) {
            return $current_dir . '/';
        }
        $current_dir = dirname($current_dir);
    }
    
    // Fallback: assume we're in includes/ directory
    return dirname(__DIR__) . '/';
}
```

### Path Helper Usage
```php
// In admin pages (admin/masjid/)
$image_path = getImagePath($article['featured_image'], 'admin');
// Result: ../../assets/uploads/articles/image.jpg

// In public pages (pages/)
$image_path = getImagePath($article['featured_image'], 'public');
// Result: ../assets/uploads/articles/image.jpg
```

## Expected Results After Fix
- **New uploads**: Files saved to `assets/uploads/articles/` (correct location)
- **Database**: Stores `assets/uploads/articles/filename.ext`
- **Admin display**: Shows `../../assets/uploads/articles/filename.ext`
- **Public display**: Shows `../assets/uploads/articles/filename.ext`
- **Browser loads**: `http://localhost/test/lms/bimbel/assets/uploads/articles/filename.ext` ✅

## Verification
1. Run `debug_image_paths.php` - should show files in correct locations
2. Upload new image - should save to correct directory
3. Check public news page - images should display correctly
4. Check admin panel - images should display correctly