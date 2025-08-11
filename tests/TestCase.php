<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use JTD\CMSBlogSystem\CMSBlogSystemServiceProvider;
use JTD\CMSBlogSystem\Tests\TestHelpers\MediaLibraryTestingTrait;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base Test Case for CMS Blog System
 *
 * Provides the foundation for all package tests with proper
 * service provider registration and environment setup.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
abstract class TestCase extends Orchestra
{
    use MediaLibraryTestingTrait;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'JTD\\CMSBlogSystem\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            \JTD\AdminPanel\AdminPanelServiceProvider::class,
            CMSBlogSystemServiceProvider::class,
            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up CMS Blog System configuration
        config()->set('cms-blog-system.framework', 'bootstrap');
        config()->set('cms-blog-system.routes.prefix', 'blog');
        config()->set('cms-blog-system.seo.enabled', true);
        config()->set('cms-blog-system.rss.enabled', true);
    }

    /**
     * Define database migrations.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Run the migrations
        $this->artisan('migrate', ['--database' => 'testing']);
    }

    /**
     * Assert that a route exists.
     */
    protected function assertRouteExists(string $routeName): void
    {
        $this->assertTrue(
            app('router')->has($routeName),
            "Route [{$routeName}] does not exist."
        );
    }

    /**
     * Assert that a configuration key exists.
     */
    protected function assertConfigExists(string $key): void
    {
        $this->assertTrue(
            config()->has($key),
            "Configuration key [{$key}] does not exist."
        );
    }

    /**
     * Assert that a view exists.
     */
    protected function assertViewExists(string $view): void
    {
        $this->assertTrue(
            view()->exists($view),
            "View [{$view}] does not exist."
        );
    }

    /**
     * Enable media library testing for tests that need it.
     * Call this in setUp() of tests that use media functionality.
     */
    protected function enableMediaLibraryTesting(): void
    {
        $this->setUpMediaLibrary();
    }
}
