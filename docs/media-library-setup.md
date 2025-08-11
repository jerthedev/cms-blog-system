# Media Library Setup

The CMS Blog System uses [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary) for handling file uploads and media management. This document explains how to set up media library support in your project.

## Quick Setup

The easiest way to set up media library support is using our setup command:

```bash
php artisan cms-blog:setup-media
```

This command will:
- ✅ Check if Spatie Media Library is installed
- ✅ Publish the media library migrations
- ✅ Run migrations to create the media table
- ✅ Optionally publish the media library configuration

## Manual Setup

If you prefer to set up media library manually:

### 1. Install Spatie Media Library

```bash
composer require spatie/laravel-medialibrary
```

### 2. Publish and Run Migrations

```bash
# Publish the migration
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"

# Run the migration
php artisan migrate
```

### 3. Optional: Publish Configuration

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"
```

## Checking Setup Status

You can check if media library is properly configured:

```bash
php artisan cms-blog:setup-media --check
```

This will show:
- 📦 Package installation status
- 🗄️ Media table existence
- ⚙️ Service provider registration
- 🎯 Overall configuration status

## Features Enabled by Media Library

Once media library is set up, blog posts support:

### Featured Images
```php
$post = BlogPost::find(1);

// Add featured image
$post->addMediaFromRequest('featured_image')
     ->toMediaCollection('featured_images');

// Get featured image URL
$featuredImageUrl = $post->getFirstMediaUrl('featured_images');
```

### Content Images
```php
// Add multiple content images
$post->addMediaFromRequest('content_images')
     ->each(fn($fileAdder) => $fileAdder->toMediaCollection('content_images'));

// Get all content images
$contentImages = $post->getMedia('content_images');
```

### Image Conversions
The blog system automatically generates these image conversions:
- **thumb**: 300x200px (cropped)
- **medium**: 600x400px (fitted)
- **large**: 1200x800px (fitted)

```php
// Get specific conversion
$thumbnailUrl = $post->getFirstMediaUrl('featured_images', 'thumb');
```

## Testing with Media Library

### In Package Tests
The package includes comprehensive media library testing utilities:

```php
use JTD\CMSBlogSystem\Tests\TestHelpers\MediaLibraryTestingTrait;

class MyTest extends TestCase
{
    use MediaLibraryTestingTrait;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableMediaLibraryTesting();
    }
    
    /** @test */
    public function it_can_attach_media(): void
    {
        $post = BlogPost::factory()->create();
        $image = $this->createFakeImage('test.jpg');
        
        $media = $post->addMedia($image->getPathname())
                     ->toMediaCollection('featured_images');
                     
        $this->assertHasMediaInCollection($post, 'featured_images', 1);
    }
}
```

### In Host Project Tests
If your host project tests need media library support:

```php
// In your test
protected function setUp(): void
{
    parent::setUp();
    
    // Ensure media library is available for testing
    if (!Schema::hasTable('media')) {
        $this->markTestSkipped('Media library not configured');
    }
}
```

## Configuration Options

### Storage Disk
Configure which disk to use for media files:

```php
// config/media-library.php
'disk_name' => env('MEDIA_DISK', 'public'),
```

### File Size Limits
```php
'max_file_size' => 1024 * 1024 * 10, // 10MB
```

### Queue Conversions
```php
'queue_conversions_by_default' => true,
```

## Troubleshooting

### Media Table Missing
```bash
# Check status
php artisan cms-blog:setup-media --check

# Set up media library
php artisan cms-blog:setup-media
```

### Migration Conflicts
If you get migration conflicts:

1. Check existing migrations:
   ```bash
   php artisan migrate:status
   ```

2. If media migration already exists, skip our setup:
   ```bash
   # Just check configuration
   php artisan cms-blog:setup-media --check
   ```

### Package Not Installed
```bash
composer require spatie/laravel-medialibrary
```

### Service Provider Not Registered
Add to `config/app.php`:
```php
'providers' => [
    // ...
    Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
],
```

## Without Media Library

The blog system works without media library, but with limited functionality:
- ✅ All core blog features work
- ❌ No file upload support
- ❌ No featured images
- ❌ No media management

To check if media library is available:
```php
if (BlogPost::hasMediaLibrarySupport()) {
    // Media library features available
} else {
    // Fallback to basic functionality
}
```

## Best Practices

1. **Always use the setup command** for new projects
2. **Check media library status** before using media features
3. **Handle missing media gracefully** in your application
4. **Use appropriate image conversions** for performance
5. **Configure proper storage disks** for production

## Support

If you encounter issues with media library setup:

1. Run the status check: `php artisan cms-blog:setup-media --check`
2. Check the [Spatie Media Library documentation](https://spatie.be/docs/laravel-medialibrary)
3. Review your Laravel version compatibility
4. Ensure proper file permissions on storage directories
