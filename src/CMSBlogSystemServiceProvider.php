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

        // Register publishing workflow services
        $this->app->singleton(
            \JTD\CMSBlogSystem\Services\PublishingWorkflowService::class,
            \JTD\CMSBlogSystem\Services\PublishingWorkflowService::class
        );

        $this->app->singleton(
            \JTD\CMSBlogSystem\Services\DraftPreviewService::class,
            \JTD\CMSBlogSystem\Services\DraftPreviewService::class
        );
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
        $this->bootAdminPanelResources();
        $this->bootEventListeners();
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
        // Always register framework-specific package views
        $framework = config('cms-blog-system.framework', 'bootstrap');
        $this->loadViewsFrom(__DIR__."/../resources/views/{$framework}", 'cms-blog-system');

        // Also load legacy views for backward compatibility
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cms-blog-system-legacy');

        // Note: If views are published to the host app, Laravel will automatically
        // prefer those over package views when using standard view names (without namespace)
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

            // Publish Bootstrap views to standard Laravel paths
            $this->publishes([
                __DIR__.'/../resources/views/bootstrap/layouts' => resource_path('views/layouts'),
                __DIR__.'/../resources/views/bootstrap/partials' => resource_path('views/partials'),
            ], 'cms-blog-system-views-bootstrap');

            // Publish Tailwind views to standard Laravel paths
            $this->publishes([
                __DIR__.'/../resources/views/tailwind/layouts' => resource_path('views/layouts'),
                __DIR__.'/../resources/views/tailwind/partials' => resource_path('views/partials'),
            ], 'cms-blog-system-views-tailwind');

            // Auto-detect framework and publish appropriate views
            $this->publishes([
                $this->getFrameworkViewPath('layouts') => resource_path('views/layouts'),
                $this->getFrameworkViewPath('partials') => resource_path('views/partials'),
            ], 'cms-blog-system-views');

            // Publish legacy views (for backward compatibility)
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/cms-blog-system'),
            ], 'cms-blog-system-views-legacy');

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
     * Boot AdminPanel resources registration.
     */
    protected function bootAdminPanelResources(): void
    {
        if (class_exists(\JTD\AdminPanel\Support\AdminPanel::class)) {
            \JTD\AdminPanel\Support\AdminPanel::resources([
                \JTD\CMSBlogSystem\Admin\Resources\BlogPostResource::class,
                \JTD\CMSBlogSystem\Admin\Resources\BlogCategoryResource::class,
                \JTD\CMSBlogSystem\Admin\Resources\BlogTagResource::class,
            ]);
        }
    }

    /**
     * Get the framework-specific view path.
     */
    private function getFrameworkViewPath(string $type = ''): string
    {
        $framework = config('cms-blog-system.framework', 'bootstrap');
        $basePath = __DIR__."/../resources/views/{$framework}";

        return $type ? "{$basePath}/{$type}" : $basePath;
    }

    /**
     * Check if views are published to the host application.
     */
    private function viewsArePublished(): bool
    {
        return file_exists(resource_path('views/layouts/single-column.blade.php'));
    }

    /**
     * Boot event listeners.
     */
    protected function bootEventListeners(): void
    {
        $this->app['events']->listen(
            \JTD\CMSBlogSystem\Events\PostPublished::class,
            \JTD\CMSBlogSystem\Listeners\SendPostPublishedNotification::class
        );
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
