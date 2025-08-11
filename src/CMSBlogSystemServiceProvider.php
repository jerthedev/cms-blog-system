<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem;

use Illuminate\Support\ServiceProvider;
use JTD\CMSBlogSystem\Configuration\BlogConfig;
use JTD\CMSBlogSystem\Console\Commands\InstallCommand;
use JTD\CMSBlogSystem\Console\Commands\SetupMediaLibraryCommand;
use JTD\CMSBlogSystem\Http\Middleware\BlogAuthenticate;
use JTD\CMSBlogSystem\Support\CMSBlogSystem;

/**
 * CMS Blog System Service Provider
 *
 * Handles package registration, configuration publishing, routes,
 * middleware, and integration with AdminPanel for the CMS Blog System.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class CMSBlogSystemServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cms-blog-system.php',
            'cms-blog-system'
        );

        $this->app->singleton(CMSBlogSystem::class, function ($app) {
            return new CMSBlogSystem($app);
        });

        // Register the configuration manager
        $this->app->singleton(BlogConfig::class, function () {
            return new BlogConfig;
        });

        $this->app->alias(CMSBlogSystem::class, 'cms-blog-system');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootConfig();
        $this->bootMigrations();
        $this->bootRoutes();
        $this->bootViews();
        $this->bootCommands();
        $this->bootMiddleware();
        $this->bootPublishing();
    }

    /**
     * Boot configuration.
     */
    protected function bootConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cms-blog-system.php',
            'cms-blog-system'
        );
    }

    /**
     * Boot database migrations.
     */
    protected function bootMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Boot routes.
     */
    protected function bootRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }

    /**
     * Boot views.
     */
    protected function bootViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cms-blog-system');
    }

    /**
     * Boot console commands.
     */
    protected function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SetupMediaLibraryCommand::class,
            ]);
        }
    }

    /**
     * Boot middleware.
     */
    protected function bootMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('blog.auth', BlogAuthenticate::class);
    }

    /**
     * Boot publishing.
     */
    protected function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/cms-blog-system.php' => config_path('cms-blog-system.php'),
            ], 'cms-blog-system-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/cms-blog-system'),
            ], 'cms-blog-system-views');

            // Publish assets
            $this->publishes([
                __DIR__.'/../resources/css' => public_path('vendor/cms-blog-system/css'),
                __DIR__.'/../resources/js' => public_path('vendor/cms-blog-system/js'),
            ], 'cms-blog-system-assets');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'cms-blog-system-migrations');

            // Publish all
            $this->publishes([
                __DIR__.'/../config/cms-blog-system.php' => config_path('cms-blog-system.php'),
                __DIR__.'/../resources/views' => resource_path('views/vendor/cms-blog-system'),
                __DIR__.'/../resources/css' => public_path('vendor/cms-blog-system/css'),
                __DIR__.'/../resources/js' => public_path('vendor/cms-blog-system/js'),
            ], 'cms-blog-system');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            CMSBlogSystem::class,
            'cms-blog-system',
        ];
    }
}
