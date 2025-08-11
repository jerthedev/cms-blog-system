<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Migration Publishing Test
 *
 * Tests that migrations can be published to host projects correctly.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class MigrationPublishingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any existing published migrations
        $this->cleanupPublishedMigrations();
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $this->cleanupPublishedMigrations();

        parent::tearDown();
    }

    /** @test */
    public function it_can_publish_migrations_to_host_project(): void
    {
        // Run the publish command
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-migrations',
            '--force' => true,
        ]);

        // Check that migration files were published
        $migrationPath = database_path('migrations');

        $this->assertTrue(
            File::exists($migrationPath),
            'Migrations directory should exist'
        );

        // Check for specific migration files (they will have timestamps)
        $files = File::files($migrationPath);
        $migrationFiles = collect($files)->map(fn ($file) => $file->getFilename());

        $this->assertTrue(
            $migrationFiles->contains(fn ($filename) => str_contains($filename, 'create_blog_posts_table')),
            'blog_posts migration should be published'
        );

        $this->assertTrue(
            $migrationFiles->contains(fn ($filename) => str_contains($filename, 'create_blog_categories_table')),
            'blog_categories migration should be published'
        );

        $this->assertTrue(
            $migrationFiles->contains(fn ($filename) => str_contains($filename, 'create_blog_tags_table')),
            'blog_tags migration should be published'
        );

        $this->assertTrue(
            $migrationFiles->contains(fn ($filename) => str_contains($filename, 'create_blog_post_categories_table')),
            'blog_post_categories migration should be published'
        );

        $this->assertTrue(
            $migrationFiles->contains(fn ($filename) => str_contains($filename, 'create_blog_post_tags_table')),
            'blog_post_tags migration should be published'
        );
    }

    /** @test */
    public function published_migrations_have_correct_content(): void
    {
        // Publish migrations
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-migrations',
            '--force' => true,
        ]);

        $migrationPath = database_path('migrations');
        $files = File::files($migrationPath);

        // Find the blog_posts migration
        $blogPostsMigration = collect($files)->first(
            fn ($file) => str_contains($file->getFilename(), 'create_blog_posts_table')
        );

        $this->assertNotNull($blogPostsMigration, 'blog_posts migration file should exist');

        $content = File::get($blogPostsMigration->getPathname());

        // Check that the migration contains expected table structure
        $this->assertStringContainsString('Schema::create(\'blog_posts\'', $content);
        $this->assertStringContainsString('$table->string(\'title\')', $content);
        $this->assertStringContainsString('$table->string(\'slug\')->unique()', $content);
        $this->assertStringContainsString('$table->longText(\'content\')', $content);
        $this->assertStringContainsString('$table->enum(\'status\'', $content);
    }

    /** @test */
    public function it_publishes_migrations_with_proper_timestamps(): void
    {
        // Publish migrations
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-migrations',
            '--force' => true,
        ]);

        $migrationPath = database_path('migrations');
        $files = File::files($migrationPath);
        $filenames = collect($files)->map(fn ($file) => $file->getFilename())->sort();

        // Check that files have proper timestamp format and are in correct order
        $blogPostsFile = $filenames->first(fn ($name) => str_contains($name, 'create_blog_posts_table'));
        $blogCategoriesFile = $filenames->first(fn ($name) => str_contains($name, 'create_blog_categories_table'));
        $blogTagsFile = $filenames->first(fn ($name) => str_contains($name, 'create_blog_tags_table'));

        $this->assertNotNull($blogPostsFile);
        $this->assertNotNull($blogCategoriesFile);
        $this->assertNotNull($blogTagsFile);

        // Check timestamp format (YYYY_MM_DD_HHMMSS)
        $this->assertMatchesRegularExpression(
            '/^\d{4}_\d{2}_\d{2}_\d{6}_create_blog_posts_table\.php$/',
            $blogPostsFile
        );
    }

    /**
     * Clean up published migration files.
     */
    protected function cleanupPublishedMigrations(): void
    {
        $migrationPath = database_path('migrations');

        if (File::exists($migrationPath)) {
            $files = File::files($migrationPath);

            foreach ($files as $file) {
                if (str_contains($file->getFilename(), 'blog_')) {
                    File::delete($file->getPathname());
                }
            }
        }
    }
}
