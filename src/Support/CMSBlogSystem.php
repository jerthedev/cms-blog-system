<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Support;

use Illuminate\Contracts\Foundation\Application;

/**
 * CMS Blog System Support Class
 *
 * Main support class for the CMS Blog System package.
 * Provides utilities and configuration access.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class CMSBlogSystem
{
    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * Create a new CMS Blog System instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the package version.
     */
    public function version(): string
    {
        return '1.0.0-dev';
    }

    /**
     * Get configuration value.
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return config("cms-blog-system.{$key}", $default);
    }

    /**
     * Check if the package is installed.
     */
    public function isInstalled(): bool
    {
        return file_exists(config_path('cms-blog-system.php'));
    }

    /**
     * Get the framework choice (bootstrap or tailwind).
     */
    public function framework(): string
    {
        return $this->config('framework', 'bootstrap');
    }

    /**
     * Check if using Bootstrap framework.
     */
    public function isBootstrap(): bool
    {
        return $this->framework() === 'bootstrap';
    }

    /**
     * Check if using Tailwind framework.
     */
    public function isTailwind(): bool
    {
        return $this->framework() === 'tailwind';
    }

    /**
     * Get the blog route prefix.
     */
    public function routePrefix(): string
    {
        return $this->config('routes.prefix', 'blog');
    }

    /**
     * Get the blog middleware.
     */
    public function middleware(): array
    {
        return $this->config('routes.middleware', ['web']);
    }

    /**
     * Check if SEO features are enabled.
     */
    public function seoEnabled(): bool
    {
        return $this->config('seo.enabled', true);
    }

    /**
     * Check if RSS feeds are enabled.
     */
    public function rssEnabled(): bool
    {
        return $this->config('rss.enabled', true);
    }

    /**
     * Get the posts per page setting.
     */
    public function postsPerPage(): int
    {
        return $this->config('pagination.posts_per_page', 10);
    }
}
