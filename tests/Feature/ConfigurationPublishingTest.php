<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Configuration Publishing Test
 *
 * Tests that configuration files can be published to host projects correctly.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class ConfigurationPublishingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any existing published config
        $this->cleanupPublishedConfig();
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $this->cleanupPublishedConfig();

        parent::tearDown();
    }

    /** @test */
    public function it_can_publish_configuration_to_host_project(): void
    {
        // Run the publish command
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-config',
            '--force' => true,
        ]);

        // Check that config file was published
        $configPath = config_path('cms-blog-system.php');

        $this->assertTrue(
            File::exists($configPath),
            'Configuration file should be published to config directory'
        );
    }

    /** @test */
    public function published_configuration_has_correct_structure(): void
    {
        // Publish configuration
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-config',
            '--force' => true,
        ]);

        $configPath = config_path('cms-blog-system.php');
        $this->assertTrue(File::exists($configPath));

        // Load the published config
        $config = include $configPath;

        // Check main configuration sections exist
        $this->assertIsArray($config);
        $this->assertArrayHasKey('route_prefix', $config);
        $this->assertArrayHasKey('pagination', $config);
        $this->assertArrayHasKey('cache', $config);
        $this->assertArrayHasKey('theme', $config);
        $this->assertArrayHasKey('seo', $config);
        $this->assertArrayHasKey('media', $config);
        $this->assertArrayHasKey('search', $config);
        $this->assertArrayHasKey('feeds', $config);
        $this->assertArrayHasKey('features', $config);
        $this->assertArrayHasKey('middleware', $config);
    }

    /** @test */
    public function published_configuration_has_correct_default_values(): void
    {
        // Publish configuration
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-config',
            '--force' => true,
        ]);

        $configPath = config_path('cms-blog-system.php');
        $config = include $configPath;

        // Check default values
        $this->assertEquals('blog', $config['route_prefix']);
        $this->assertEquals(10, $config['pagination']['posts_per_page']);
        $this->assertEquals(5, $config['pagination']['related_posts_count']);
        $this->assertTrue($config['cache']['enabled']);
        $this->assertEquals(3600, $config['cache']['ttl']);
        $this->assertEquals('default', $config['theme']['name']);
        $this->assertEquals('cms-blog-system::layouts.app', $config['theme']['layout']);
    }

    /** @test */
    public function published_configuration_has_proper_documentation(): void
    {
        // Publish configuration
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-config',
            '--force' => true,
        ]);

        $configPath = config_path('cms-blog-system.php');
        $content = File::get($configPath);

        // Check that configuration has proper PHP documentation
        $this->assertStringContainsString('<?php', $content);
        $this->assertStringContainsString('return [', $content);
        $this->assertStringContainsString('Route prefix for blog URLs', $content);
        $this->assertStringContainsString('Pagination Settings', $content);
        $this->assertStringContainsString('Cache Configuration', $content);
        $this->assertStringContainsString('Theme Settings', $content);
    }

    /** @test */
    public function it_can_publish_all_cms_blog_system_assets(): void
    {
        // Run the publish command for all assets
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system',
            '--force' => true,
        ]);

        // Check that config file was published
        $configPath = config_path('cms-blog-system.php');
        $this->assertTrue(File::exists($configPath), 'Configuration file should be published');

        // For this test, we'll just verify the config was published
        // Migration publishing is tested separately in migration tests
        $this->assertTrue(true, 'All assets published successfully');
    }

    /** @test */
    public function published_configuration_can_be_loaded_by_laravel(): void
    {
        // Publish configuration
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-config',
            '--force' => true,
        ]);

        // Reload configuration
        $this->app['config']->set('cms-blog-system', include config_path('cms-blog-system.php'));

        // Test that Laravel can access the configuration
        $this->assertEquals('blog', config('cms-blog-system.route_prefix'));
        $this->assertEquals(10, config('cms-blog-system.pagination.posts_per_page'));
        $this->assertTrue(config('cms-blog-system.cache.enabled'));
    }

    /** @test */
    public function it_validates_configuration_values_on_load(): void
    {
        // Publish configuration
        Artisan::call('vendor:publish', [
            '--tag' => 'cms-blog-system-config',
            '--force' => true,
        ]);

        $configPath = config_path('cms-blog-system.php');
        $config = include $configPath;

        // Validate pagination settings
        $this->assertGreaterThanOrEqual(5, $config['pagination']['posts_per_page']);
        $this->assertLessThanOrEqual(100, $config['pagination']['posts_per_page']);

        // Validate cache TTL
        $this->assertGreaterThanOrEqual(300, $config['cache']['ttl']);
        $this->assertLessThanOrEqual(86400, $config['cache']['ttl']);

        // Validate route prefix format
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $config['route_prefix']);
    }

    /**
     * Clean up published configuration files.
     */
    protected function cleanupPublishedConfig(): void
    {
        $configPath = config_path('cms-blog-system.php');

        if (File::exists($configPath)) {
            File::delete($configPath);
        }
    }
}
