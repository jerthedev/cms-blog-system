<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\TestHelpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Media Library Testing Trait
 *
 * Provides utilities for testing with Spatie Media Library.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
trait MediaLibraryTestingTrait
{
    /**
     * Set up media library for testing.
     */
    protected function setUpMediaLibrary(): void
    {
        // Check if media library is properly configured in host project
        if ($this->isMediaLibraryConfigured()) {
            $this->configureMediaLibraryForTesting();

            return;
        }

        // Only set up media table in testing environment if not configured
        if (app()->environment('testing')) {
            $this->setUpMediaLibraryForTesting();
        } else {
            throw new \RuntimeException(
                'Media library is not configured. Please run: php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations" && php artisan migrate'
            );
        }
    }

    /**
     * Set up media library specifically for testing environment.
     */
    protected function setUpMediaLibraryForTesting(): void
    {
        // Configure media library for testing
        Config::set('media-library.disk_name', 'testing');
        Config::set('media-library.max_file_size', 1024 * 1024 * 10); // 10MB
        Config::set('media-library.queue_conversions_by_default', false);

        // Set up testing disk
        Config::set('filesystems.disks.testing', [
            'driver' => 'local',
            'root' => storage_path('app/testing'),
            'url' => env('APP_URL').'/storage/testing',
            'visibility' => 'public',
        ]);

        // Only create media table if it doesn't exist (testing only)
        $this->ensureMediaTableExists();
    }

    /**
     * Clean up media library after testing.
     */
    protected function tearDownMediaLibrary(): void
    {
        // Clean up test files
        if (Storage::disk('testing')->exists('')) {
            Storage::disk('testing')->deleteDirectory('');
        }

        // Clean up media records
        if (Schema::hasTable('media')) {
            Media::truncate();
        }
    }

    /**
     * Check if media library is properly configured.
     */
    protected function isMediaLibraryConfigured(): bool
    {
        return Schema::hasTable('media') &&
               class_exists(\Spatie\MediaLibrary\MediaCollections\Models\Media::class);
    }

    /**
     * Ensure media table exists for testing only.
     * This creates a minimal media table structure for testing purposes.
     */
    protected function ensureMediaTableExists(): void
    {
        if (! Schema::hasTable('media')) {
            Schema::create('media', function ($table) {
                $table->id();
                $table->morphs('model');
                $table->uuid()->nullable()->unique();
                $table->string('collection_name');
                $table->string('name');
                $table->string('file_name');
                $table->string('mime_type')->nullable();
                $table->string('disk');
                $table->string('conversions_disk')->nullable();
                $table->unsignedBigInteger('size');
                $table->json('manipulations');
                $table->json('custom_properties');
                $table->json('generated_conversions');
                $table->json('responsive_images');
                $table->unsignedInteger('order_column')->nullable()->index();
                $table->nullableTimestamps();
            });
        }
    }

    /**
     * Create a fake image file for testing.
     */
    protected function createFakeImage(string $name = 'test-image.jpg', int $width = 800, int $height = 600): UploadedFile
    {
        return UploadedFile::fake()->image($name, $width, $height);
    }

    /**
     * Create a fake document file for testing.
     */
    protected function createFakeDocument(string $name = 'test-document.pdf', int $kilobytes = 100): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes);
    }

    /**
     * Assert that a model has media in a specific collection.
     */
    protected function assertHasMediaInCollection($model, string $collection, ?int $expectedCount = null): void
    {
        $mediaCount = $model->getMedia($collection)->count();

        if ($expectedCount !== null) {
            $this->assertEquals(
                $expectedCount,
                $mediaCount,
                "Expected {$expectedCount} media items in '{$collection}' collection, found {$mediaCount}"
            );
        } else {
            $this->assertGreaterThan(
                0,
                $mediaCount,
                "Expected media items in '{$collection}' collection, found none"
            );
        }
    }

    /**
     * Assert that a model has no media in a specific collection.
     */
    protected function assertHasNoMediaInCollection($model, string $collection): void
    {
        $mediaCount = $model->getMedia($collection)->count();

        $this->assertEquals(
            0,
            $mediaCount,
            "Expected no media items in '{$collection}' collection, found {$mediaCount}"
        );
    }

    /**
     * Assert that media has the expected properties.
     */
    protected function assertMediaProperties(Media $media, array $expectedProperties): void
    {
        foreach ($expectedProperties as $property => $expectedValue) {
            $actualValue = $media->$property;

            $this->assertEquals(
                $expectedValue,
                $actualValue,
                "Expected media {$property} to be '{$expectedValue}', got '{$actualValue}'"
            );
        }
    }

    /**
     * Assert that media conversions were generated.
     */
    protected function assertMediaConversions(Media $media, array $expectedConversions): void
    {
        $generatedConversions = $media->generated_conversions;

        foreach ($expectedConversions as $conversion) {
            $this->assertArrayHasKey(
                $conversion,
                $generatedConversions,
                "Expected conversion '{$conversion}' to be generated"
            );

            $this->assertTrue(
                $generatedConversions[$conversion],
                "Expected conversion '{$conversion}' to be successfully generated"
            );
        }
    }

    /**
     * Skip test if media library is not properly configured.
     */
    protected function skipIfMediaLibraryNotConfigured(): void
    {
        if (! Schema::hasTable('media')) {
            $this->markTestSkipped('Media library table not available');
        }

        if (! Storage::disk('testing')->exists('')) {
            try {
                Storage::disk('testing')->makeDirectory('');
            } catch (\Exception $e) {
                $this->markTestSkipped('Cannot create testing storage directory');
            }
        }
    }

    /**
     * Create media for a model without actual file upload.
     */
    protected function createMediaForModel($model, string $collection = 'default', array $attributes = []): Media
    {
        $defaultAttributes = [
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'collection_name' => $collection,
            'name' => 'test-file',
            'file_name' => 'test-file.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'testing',
            'size' => 1024,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ];

        $attributes = array_merge($defaultAttributes, $attributes);

        return Media::create($attributes);
    }

    /**
     * Mock media library functionality for testing.
     */
    protected function mockMediaLibrary(): void
    {
        // Mock the media repository
        $this->app->bind(\Spatie\MediaLibrary\MediaCollections\MediaRepository::class, function () {
            return new class
            {
                public function all()
                {
                    return collect();
                }

                public function getByModelType(string $modelType)
                {
                    return collect();
                }

                public function getByIds(array $ids)
                {
                    return collect();
                }

                public function getByIdAndCollectionName(int $id, string $collectionName) {}
            };
        });
    }

    /**
     * Assert that media files exist on disk.
     */
    protected function assertMediaFilesExist(Media $media): void
    {
        $this->assertTrue(
            Storage::disk($media->disk)->exists($media->getPath()),
            "Media file should exist at path: {$media->getPath()}"
        );
    }

    /**
     * Assert that media files do not exist on disk.
     */
    protected function assertMediaFilesDoNotExist(Media $media): void
    {
        $this->assertFalse(
            Storage::disk($media->disk)->exists($media->getPath()),
            "Media file should not exist at path: {$media->getPath()}"
        );
    }

    /**
     * Get media library configuration for testing.
     */
    protected function getMediaLibraryTestConfig(): array
    {
        return [
            'disk_name' => 'testing',
            'max_file_size' => 1024 * 1024 * 10,
            'queue_conversions_by_default' => false,
            'media_model' => Media::class,
            'temporary_upload_path' => storage_path('app/temp'),
            'generate_thumbnails_for_temporary_uploads' => false,
        ];
    }

    /**
     * Set up media library configuration for testing.
     */
    protected function configureMediaLibraryForTesting(): void
    {
        $config = $this->getMediaLibraryTestConfig();

        foreach ($config as $key => $value) {
            Config::set("media-library.{$key}", $value);
        }
    }
}
