<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Configuration;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * Blog Configuration Manager
 *
 * Manages configuration settings for the blog system with validation
 * and default values.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogConfig
{
    /**
     * Configuration key prefix.
     */
    protected const CONFIG_KEY = 'cms-blog-system';

    /**
     * Default configuration values.
     */
    protected const DEFAULTS = [
        'route_prefix' => 'blog',
        'pagination' => [
            'posts_per_page' => 10,
            'related_posts_count' => 5,
            'tag_cloud_limit' => 20,
        ],
        'cache' => [
            'enabled' => true,
            'ttl' => 3600,
        ],
        'theme' => [
            'name' => 'default',
            'layout' => 'cms-blog-system::layouts.app',
        ],
        'seo' => [
            'meta_title_suffix' => '',
            'generate_sitemap' => true,
            'sitemap_frequency' => 'weekly',
        ],
        'media' => [
            'disk' => 'public',
            'featured_image_sizes' => [
                'thumb' => [300, 200],
                'medium' => [600, 400],
                'large' => [1200, 800],
            ],
        ],
        'search' => [
            'driver' => 'database',
            'min_query_length' => 2,
            'max_results' => 100,
        ],
        'feeds' => [
            'enabled' => true,
            'title' => 'Blog Feed',
            'description' => 'Latest blog posts',
            'items_count' => 15,
        ],
        'features' => [
            'comments_enabled' => false,
            'social_sharing' => true,
            'reading_time' => true,
        ],
        'middleware' => [
            'web' => ['web'],
            'api' => ['api'],
        ],
    ];

    /**
     * Get route prefix for blog URLs.
     */
    public function getRoutePrefix(): string
    {
        $prefix = $this->get('route_prefix', self::DEFAULTS['route_prefix']);

        // Sanitize route prefix
        $prefix = Str::slug($prefix);

        return empty($prefix) ? self::DEFAULTS['route_prefix'] : $prefix;
    }

    /**
     * Get posts per page for pagination.
     */
    public function getPostsPerPage(): int
    {
        $perPage = $this->get('pagination.posts_per_page', self::DEFAULTS['pagination']['posts_per_page']);

        // Validate range (5-100)
        return max(5, min(100, (int) $perPage));
    }

    /**
     * Get related posts count.
     */
    public function getRelatedPostsCount(): int
    {
        return (int) $this->get('pagination.related_posts_count', self::DEFAULTS['pagination']['related_posts_count']);
    }

    /**
     * Get tag cloud limit.
     */
    public function getTagCloudLimit(): int
    {
        return (int) $this->get('pagination.tag_cloud_limit', self::DEFAULTS['pagination']['tag_cloud_limit']);
    }

    /**
     * Check if cache is enabled.
     */
    public function isCacheEnabled(): bool
    {
        return (bool) $this->get('cache.enabled', self::DEFAULTS['cache']['enabled']);
    }

    /**
     * Get cache TTL in seconds.
     */
    public function getCacheTtl(): int
    {
        $ttl = $this->get('cache.ttl', self::DEFAULTS['cache']['ttl']);

        // Validate range (5 minutes to 24 hours)
        return max(300, min(86400, (int) $ttl));
    }

    /**
     * Get theme name.
     */
    public function getThemeName(): string
    {
        return $this->get('theme.name', self::DEFAULTS['theme']['name']);
    }

    /**
     * Get theme layout.
     */
    public function getThemeLayout(): string
    {
        return $this->get('theme.layout', self::DEFAULTS['theme']['layout']);
    }

    /**
     * Get meta title suffix.
     */
    public function getMetaTitleSuffix(): string
    {
        return $this->get('seo.meta_title_suffix', self::DEFAULTS['seo']['meta_title_suffix']);
    }

    /**
     * Check if sitemap generation is enabled.
     */
    public function shouldGenerateSitemap(): bool
    {
        return (bool) $this->get('seo.generate_sitemap', self::DEFAULTS['seo']['generate_sitemap']);
    }

    /**
     * Get sitemap frequency.
     */
    public function getSitemapFrequency(): string
    {
        return $this->get('seo.sitemap_frequency', self::DEFAULTS['seo']['sitemap_frequency']);
    }

    /**
     * Get media disk.
     */
    public function getMediaDisk(): string
    {
        return $this->get('media.disk', self::DEFAULTS['media']['disk']);
    }

    /**
     * Get featured image sizes.
     */
    public function getFeaturedImageSizes(): array
    {
        return $this->get('media.featured_image_sizes', self::DEFAULTS['media']['featured_image_sizes']);
    }

    /**
     * Get search driver.
     */
    public function getSearchDriver(): string
    {
        return $this->get('search.driver', self::DEFAULTS['search']['driver']);
    }

    /**
     * Get minimum query length for search.
     */
    public function getMinQueryLength(): int
    {
        return (int) $this->get('search.min_query_length', self::DEFAULTS['search']['min_query_length']);
    }

    /**
     * Get maximum search results.
     */
    public function getMaxSearchResults(): int
    {
        return (int) $this->get('search.max_results', self::DEFAULTS['search']['max_results']);
    }

    /**
     * Check if feeds are enabled.
     */
    public function areFeedsEnabled(): bool
    {
        return (bool) $this->get('feeds.enabled', self::DEFAULTS['feeds']['enabled']);
    }

    /**
     * Get feed title.
     */
    public function getFeedTitle(): string
    {
        return $this->get('feeds.title', self::DEFAULTS['feeds']['title']);
    }

    /**
     * Get feed description.
     */
    public function getFeedDescription(): string
    {
        return $this->get('feeds.description', self::DEFAULTS['feeds']['description']);
    }

    /**
     * Get feed items count.
     */
    public function getFeedItemsCount(): int
    {
        return (int) $this->get('feeds.items_count', self::DEFAULTS['feeds']['items_count']);
    }

    /**
     * Check if a feature is enabled.
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return (bool) $this->get("features.{$feature}", false);
    }

    /**
     * Get web middleware.
     */
    public function getWebMiddleware(): array
    {
        return $this->get('middleware.web', self::DEFAULTS['middleware']['web']);
    }

    /**
     * Get API middleware.
     */
    public function getApiMiddleware(): array
    {
        return $this->get('middleware.api', self::DEFAULTS['middleware']['api']);
    }

    /**
     * Get all configuration as array.
     */
    public function toArray(): array
    {
        return [
            'route_prefix' => $this->getRoutePrefix(),
            'pagination' => [
                'posts_per_page' => $this->getPostsPerPage(),
                'related_posts_count' => $this->getRelatedPostsCount(),
                'tag_cloud_limit' => $this->getTagCloudLimit(),
            ],
            'cache' => [
                'enabled' => $this->isCacheEnabled(),
                'ttl' => $this->getCacheTtl(),
            ],
            'theme' => [
                'name' => $this->getThemeName(),
                'layout' => $this->getThemeLayout(),
            ],
            'seo' => [
                'meta_title_suffix' => $this->getMetaTitleSuffix(),
                'generate_sitemap' => $this->shouldGenerateSitemap(),
                'sitemap_frequency' => $this->getSitemapFrequency(),
            ],
            'media' => [
                'disk' => $this->getMediaDisk(),
                'featured_image_sizes' => $this->getFeaturedImageSizes(),
            ],
            'search' => [
                'driver' => $this->getSearchDriver(),
                'min_query_length' => $this->getMinQueryLength(),
                'max_results' => $this->getMaxSearchResults(),
            ],
            'feeds' => [
                'enabled' => $this->areFeedsEnabled(),
                'title' => $this->getFeedTitle(),
                'description' => $this->getFeedDescription(),
                'items_count' => $this->getFeedItemsCount(),
            ],
            'features' => $this->get('features', self::DEFAULTS['features']),
            'middleware' => [
                'web' => $this->getWebMiddleware(),
                'api' => $this->getApiMiddleware(),
            ],
        ];
    }

    /**
     * Get configuration value with dot notation.
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return Config::get(self::CONFIG_KEY.'.'.$key, $default);
    }
}
