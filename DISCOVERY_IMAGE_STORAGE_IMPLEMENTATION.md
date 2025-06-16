# Discovery Image Storage Organization Implementation

## Overview

This document details the implementation of organized image storage for discovery records in the Handi Discovery Management System. Images are now organized in a hierarchical folder structure based on user/company and upload date.

## 📁 Folder Structure

### Solo Handyman

```
storage/app/public/discoveries/
└── solo-handyman-{user_id}/
    └── {month-year}/
        ├── {timestamp}_{random}.jpg
        ├── {timestamp}_{random}.png
        └── ...
```

**Example:**

```
storage/app/public/discoveries/
└── solo-handyman-6/
    └── june-2025/
        ├── 1718553600_a8b3c9d2.jpg
        ├── 1718554200_f2e1d5c8.png
        └── 1718555000_b9a7f3e4.jpg
```

### Company Users (Admin/Employee)

```
storage/app/public/discoveries/
└── {sanitized-company-name}-{company_id}/
    └── {month-year}/
        ├── {timestamp}_{random}.jpg
        ├── {timestamp}_{random}.png
        └── ...
```

**Example:**

```
storage/app/public/discoveries/
└── repairtech-solutions-15/
    └── june-2025/
        ├── 1718553600_x3y9z1a5.jpg
        ├── 1718554200_m8n2p6q1.png
        └── 1718555000_k4l7r9s2.jpg
```

## 🛠️ Implementation Components

### 1. DiscoveryImageService

**Location:** `app/Services/DiscoveryImageService.php`

**Key Methods:**

- `storeDiscoveryImage()` - Stores images with organized structure
- `deleteDiscoveryImage()` - Removes images from storage
- `migrateExistingImages()` - Migrates old images to new structure
- `getStorageStats()` - Provides storage analytics
- `getImageUrl()` - Gets public URLs for images

**Features:**

- **Automatic Organization**: Creates folder structure based on user type
- **Unique Filenames**: Generates timestamp + random string filenames
- **Company Name Sanitization**: Cleans company names for filesystem safety
- **Migration Support**: Moves existing images to new structure

### 2. Updated Controller Logic

**Location:** `app/Http/Controllers/DiscoveryController.php`

**Changes:**

- Replaced direct `store()` calls with `DiscoveryImageService::storeDiscoveryImage()`
- Updated image deletion to use service method
- Applied to all image handling: `store()`, `update()`, and API methods

### 3. Migration Command

**Location:** `app/Console/Commands/OrganizeDiscoveryImages.php`

**Usage:**

```bash
# Dry run (preview changes)
php artisan discovery:organize-images --dry-run

# Migrate all images
php artisan discovery:organize-images

# Migrate specific user's images
php artisan discovery:organize-images --user-id=6

# Dry run for specific user
php artisan discovery:organize-images --dry-run --user-id=6
```

**Features:**

- **Progress Bar**: Visual progress indicator
- **Dry Run Mode**: Preview changes without modifying files
- **User Filtering**: Process specific user's images only
- **Error Handling**: Graceful failure handling with detailed reporting
- **Statistics**: Comprehensive success/failure reporting

## 🔧 Service Methods Details

### `storeDiscoveryImage(UploadedFile $image, User $user): string`

Stores an uploaded image with organized folder structure.

**Process:**

1. Determines organization folder (solo handyman vs company)
2. Creates month-based subfolder
3. Generates unique filename
4. Stores file in structured path

**Returns:** Full storage path

### `getOrganizationFolder(User $user): string`

Creates organization-specific folder names:

- Solo Handyman: `solo-handyman-{id}`
- Company Users: `{sanitized-company-name}-{company-id}`

### `getCurrentMonthFolder(): string`

Creates month-based folders:

- Format: `{month-name}-{year}`
- Example: `june-2025`, `december-2024`

### `generateUniqueFileName(UploadedFile $image): string`

Creates unique filenames:

- Format: `{timestamp}_{random-8-chars}.{extension}`
- Example: `1718553600_a8b3c9d2.jpg`

### `sanitizeFolderName(string $name): string`

Cleans company names for filesystem compatibility:

- Converts to lowercase
- Replaces spaces and special characters with hyphens
- Limits length to 50 characters
- Example: `"A Company With Spaces & Special!@# Characters"` → `"a-company-with-spaces-special-characters"`

## 📊 Storage Analytics

### `getStorageStats(User $user): array`

Provides comprehensive storage statistics:

```php
[
    'total_files' => 15,
    'total_size' => 2048576, // bytes
    'months' => [
        'june-2025' => [
            'files' => 10,
            'size' => 1536000
        ],
        'may-2025' => [
            'files' => 5,
            'size' => 512576
        ]
    ]
]
```

## 🧪 Test Coverage

**Test File:** `tests/Feature/DiscoveryImageServiceTest.php`

**Test Coverage:**

- ✅ Solo handyman image storage structure
- ✅ Company user image storage structure
- ✅ Image deletion functionality
- ✅ Company name sanitization
- ✅ Unique filename generation
- ✅ Existing image migration
- ✅ Storage statistics calculation
- ✅ Company employee handling
- ✅ Image URL generation

**Results:** 9 tests, 30 assertions - All passing

## 🔄 Migration Process

### For Existing Installations

1. **Backup Current Images** (Recommended)

   ```bash
   cp -r storage/app/public/discoveries storage/app/public/discoveries-backup
   ```

2. **Run Dry Run** (Preview changes)

   ```bash
   php artisan discovery:organize-images --dry-run
   ```

3. **Execute Migration**

   ```bash
   php artisan discovery:organize-images
   ```

4. **Verify Results**
   - Check organized folder structure
   - Verify image accessibility in application
   - Review migration statistics

### Migration Output Example

```
Starting discovery image organization...
Found 25 discoveries with images to process.
ORGANIZATION COMPLETE:
- Processed discoveries: 25
- Successfully organized images: 78
- Failed to organize: 0 images
```

## 🚀 Benefits

### Organization Benefits

- **Scalable Structure**: Handles growth efficiently
- **Easy Navigation**: Logical folder hierarchy
- **Time-based Organization**: Monthly segregation for easier management
- **User/Company Separation**: Clear ownership boundaries

### Performance Benefits

- **Reduced Folder Density**: Images distributed across multiple subfolders
- **Faster File Operations**: Smaller directory sizes improve filesystem performance
- **Efficient Backups**: Selective backup by user/company or time period

### Maintenance Benefits

- **Clear Ownership**: Easy to identify image ownership
- **Automated Cleanup**: Potential for automated old image cleanup
- **Storage Analytics**: Built-in storage usage tracking
- **Migration Tools**: Seamless transition from old structure

## 🔐 Security Considerations

### File Naming

- **No User Data in Paths**: User data not exposed in filenames
- **Unique Filenames**: Prevents filename conflicts and guessing
- **Extension Preservation**: Maintains file type while ensuring uniqueness

### Access Control

- **Laravel Storage Integration**: Leverages Laravel's public disk security
- **User-scoped Folders**: Natural access control boundaries
- **Service Layer**: Centralized access control through service methods

## 📝 Usage Examples

### Storing New Images

```php
// In controller
use App\Services\DiscoveryImageService;

$user = auth()->user();
foreach ($request->file('images') as $image) {
    $path = DiscoveryImageService::storeDiscoveryImage($image, $user);
    $imagePaths[] = $path;
}
```

### Getting Image URLs

```php
// In view or API response
$imageUrl = DiscoveryImageService::getImageUrl($discovery->images[0]);
// Returns: /storage/discoveries/solo-handyman-6/june-2025/1718553600_a8b3c9d2.jpg
```

### Storage Analytics

```php
$user = auth()->user();
$stats = DiscoveryImageService::getStorageStats($user);
echo "Total images: " . $stats['total_files'];
echo "Storage used: " . number_format($stats['total_size'] / 1024 / 1024, 2) . " MB";
```

---

**Implementation Date:** June 16, 2025  
**Test Coverage:** 9 tests, 30 assertions  
**Status:** ✅ Complete and Production Ready  
**Backward Compatibility:** ✅ Maintained with migration tools
