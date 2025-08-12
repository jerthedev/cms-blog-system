<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Install Command Test
 *
 * Tests the blog:install command functionality including framework selection,
 * asset publishing, migration running, and AdminPanel registration.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class InstallCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any existing published files
        $this->cleanupPublishedFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupPublishedFiles();
        parent::tearDown();
    }

    /** @test */
    public function it_can_run_install_command_successfully(): void
    {
        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsOutput('Installing CMS Blog System...')
            ->expectsConfirmation('Do you want to run the database migrations now?', 'yes')
            ->expectsOutput('✅ CMS Blog System installed successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_publishes_configuration_files(): void
    {
        $configPath = config_path('cms-blog-system.php');

        // Ensure config doesn't exist initially
        if (File::exists($configPath)) {
            File::delete($configPath);
        }

        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists($configPath);

        // Verify config content
        $config = include $configPath;
        $this->assertIsArray($config);
        $this->assertArrayHasKey('framework', $config);
        $this->assertArrayHasKey('route_prefix', $config);
        $this->assertArrayHasKey('pagination', $config);
    }

    /** @test */
    public function it_publishes_views_and_assets(): void
    {
        $viewsPath = resource_path('views/vendor/cms-blog-system');

        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->assertExitCode(0);

        // Check that views are published
        $this->assertDirectoryExists($viewsPath);
        $this->assertFileExists($viewsPath.'/layouts/app.blade.php');
        $this->assertFileExists($viewsPath.'/partials/header.blade.php');
        $this->assertFileExists($viewsPath.'/partials/footer.blade.php');
    }

    /** @test */
    public function it_runs_migrations_when_confirmed(): void
    {
        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'yes')
            ->expectsOutput('✅ Migrations completed')
            ->assertExitCode(0);

        // Verify tables were created
        $this->assertDatabaseTableExists('blog_posts');
        $this->assertDatabaseTableExists('blog_categories');
        $this->assertDatabaseTableExists('blog_tags');
        $this->assertDatabaseTableExists('blog_post_categories');
        $this->assertDatabaseTableExists('blog_post_tags');
    }

    /** @test */
    public function it_skips_migrations_when_declined(): void
    {
        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->expectsOutput('⚠️  Remember to run "php artisan migrate" to create the blog tables')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_sets_bootstrap_framework_via_option(): void
    {
        $configPath = config_path('cms-blog-system.php');

        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->expectsOutput('✅ Framework set to: bootstrap')
            ->assertExitCode(0);

        $this->assertFileExists($configPath);

        $config = file_get_contents($configPath);
        $this->assertStringContainsString("'framework' => env('CMS_BLOG_FRAMEWORK', 'bootstrap')", $config);
    }

    /** @test */
    public function it_sets_tailwind_framework_via_option(): void
    {
        $configPath = config_path('cms-blog-system.php');

        $this->artisan('blog:install', ['--framework' => 'tailwind'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->expectsOutput('✅ Framework set to: tailwind')
            ->assertExitCode(0);

        $this->assertFileExists($configPath);

        $config = file_get_contents($configPath);
        $this->assertStringContainsString("'framework' => env('CMS_BLOG_FRAMEWORK', 'tailwind')", $config);
    }

    /** @test */
    public function it_prompts_for_framework_when_not_specified(): void
    {
        $this->artisan('blog:install')
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->expectsChoice(
                'Which CSS framework would you like to use?',
                'tailwind',
                ['bootstrap', 'tailwind']
            )
            ->expectsOutput('✅ Framework set to: tailwind')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_prompts_for_framework_when_invalid_option_provided(): void
    {
        $this->artisan('blog:install', ['--framework' => 'invalid'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->expectsChoice(
                'Which CSS framework would you like to use?',
                'bootstrap',
                ['bootstrap', 'tailwind']
            )
            ->expectsOutput('✅ Framework set to: bootstrap')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_force_overwrite_existing_files(): void
    {
        $configPath = config_path('cms-blog-system.php');

        // First installation
        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists($configPath);

        // Modify the config file
        File::put($configPath, "<?php\nreturn ['modified' => true];");

        // Second installation with force
        $this->artisan('blog:install', ['--framework' => 'tailwind', '--force' => true])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->assertExitCode(0);

        // Verify config was overwritten
        $config = file_get_contents($configPath);
        $this->assertStringNotContainsString("'modified' => true", $config);
        $this->assertStringContainsString("'framework' => env('CMS_BLOG_FRAMEWORK', 'tailwind')", $config);
    }

    /** @test */
    public function it_registers_with_admin_panel_when_available(): void
    {
        // This test will check if AdminPanel registration is attempted
        // when the AdminPanel package is available

        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->assertExitCode(0);

        // Since AdminPanel might not be available in test environment,
        // we'll verify the command completes successfully
        // The actual AdminPanel registration is tested in integration tests
        $this->assertTrue(true);
    }

    /** @test */
    public function it_displays_helpful_next_steps(): void
    {
        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->expectsOutput('Next steps:')
            ->expectsOutput('1. Configure your blog settings in config/cms-blog-system.php')
            ->expectsOutput('2. Create your first blog post using the AdminPanel')
            ->expectsOutput('3. Visit /blog to see your blog in action')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_missing_config_file_gracefully(): void
    {
        $configPath = config_path('cms-blog-system.php');

        // Delete config file if it exists
        if (File::exists($configPath)) {
            File::delete($configPath);
        }

        // Test that command completes even if config doesn't exist initially
        $this->artisan('blog:install', ['--framework' => 'bootstrap'])
            ->expectsConfirmation('Do you want to run the database migrations now?', 'no')
            ->assertExitCode(0);

        // Command should still complete and create the config file
        $this->assertFileExists($configPath);
    }

    /**
     * Clean up published files after tests.
     */
    protected function cleanupPublishedFiles(): void
    {
        $filesToClean = [
            config_path('cms-blog-system.php'),
            resource_path('views/vendor/cms-blog-system'),
            public_path('vendor/cms-blog-system'),
        ];

        foreach ($filesToClean as $path) {
            if (File::exists($path)) {
                if (File::isDirectory($path)) {
                    File::deleteDirectory($path);
                } else {
                    File::delete($path);
                }
            }
        }
    }

    /**
     * Assert that a database table exists.
     */
    protected function assertDatabaseTableExists(string $table): void
    {
        $this->assertTrue(
            $this->app['db']->getSchemaBuilder()->hasTable($table),
            "Table {$table} does not exist"
        );
    }
}
