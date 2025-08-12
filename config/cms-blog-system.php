<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Route prefix for blog URLs. This will be used as the base URL segment
    | for all blog routes. For example, with 'blog' prefix, posts will be
    | accessible at /blog/post-slug
    |
    */

    'route_prefix' => env('BLOG_ROUTE_PREFIX', 'blog'),

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Configure pagination and content limits for various blog components.
    |
    */

    'pagination' => [
        // Number of posts per page (5-100)
        'posts_per_page' => env('BLOG_POSTS_PER_PAGE', 10),

        // Number of related posts to show (1-20)
        'related_posts_count' => env('BLOG_RELATED_POSTS_COUNT', 5),

        // Maximum number of tags in tag cloud (10-50)
        'tag_cloud_limit' => env('BLOG_TAG_CLOUD_LIMIT', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for blog content to improve performance.
    |
    */

    'cache' => [
        // Enable/disable caching
        'enabled' => env('BLOG_CACHE_ENABLED', true),

        // Cache TTL in seconds (300-86400, 5 minutes to 24 hours)
        'ttl' => env('BLOG_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Framework Configuration
    |--------------------------------------------------------------------------
    |
    | Choose the CSS framework for blog templates and styling.
    |
    */

    'framework' => env('CMS_BLOG_FRAMEWORK', 'bootstrap'),

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    |
    | Configure the appearance and layout of blog pages.
    |
    */

    'theme' => [
        // Theme name for customization
        'name' => env('BLOG_THEME_NAME', 'default'),

        // Main layout template
        'layout' => env('BLOG_THEME_LAYOUT', 'cms-blog-system::layouts.app'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Search engine optimization settings for better discoverability.
    |
    */

    'seo' => [
        // Suffix to append to all page titles
        'meta_title_suffix' => env('BLOG_META_TITLE_SUFFIX', ''),

        // Generate XML sitemap automatically
        'generate_sitemap' => env('BLOG_GENERATE_SITEMAP', true),

        // Sitemap update frequency (always, hourly, daily, weekly, monthly, yearly, never)
        'sitemap_frequency' => env('BLOG_SITEMAP_FREQUENCY', 'weekly'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configure search functionality for blog content.
    |
    */

    'search' => [
        // Search driver (database, algolia, meilisearch, etc.)
        'driver' => env('BLOG_SEARCH_DRIVER', 'database'),

        // Minimum query length for search
        'min_query_length' => env('BLOG_SEARCH_MIN_LENGTH', 2),

        // Maximum number of search results
        'max_results' => env('BLOG_SEARCH_MAX_RESULTS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feed Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for RSS/Atom feed generation.
    |
    */

    'feeds' => [
        // Enable/disable feed generation
        'enabled' => env('BLOG_FEEDS_ENABLED', true),

        // Feed title
        'title' => env('BLOG_FEED_TITLE', 'Blog Feed'),

        // Feed description
        'description' => env('BLOG_FEED_DESCRIPTION', 'Latest blog posts'),

        // Number of items in feed
        'items_count' => env('BLOG_FEED_ITEMS_COUNT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for handling images and other media files.
    |
    */

    'media' => [
        // Filesystem disk for storing media files
        'disk' => env('BLOG_MEDIA_DISK', 'public'),

        // Featured image sizes for responsive images
        'featured_image_sizes' => [
            'thumb' => [300, 200],
            'medium' => [600, 400],
            'large' => [1200, 800],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific blog features.
    |
    */

    'features' => [
        // Enable comments system
        'comments_enabled' => env('BLOG_COMMENTS_ENABLED', false),

        // Enable social sharing buttons
        'social_sharing' => env('BLOG_SOCIAL_SHARING', true),

        // Show estimated reading time
        'reading_time' => env('BLOG_READING_TIME', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Middleware groups for web and API routes.
    |
    */

    'middleware' => [
        // Middleware for web routes
        'web' => ['web'],

        // Middleware for API routes
        'api' => ['api'],
    ],

];
