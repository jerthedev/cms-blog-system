<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Configuration;

use Illuminate\Support\Facades\Config;
use JTD\CMSBlogSystem\Configuration\BlogConfig;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Blog Configuration Test
 *
 * Tests the blog configuration system including default values,
 * validation, and configuration publishing.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogConfigTest extends TestCase
{
    /** @test */
    public function it_can_get_default_configuration_values(): void
    {
        $config = new BlogConfig;

        $this->assertEquals('blog', $config->getRoutePrefix());
        $this->assertEquals(10, $config->getPostsPerPage());
        $this->assertEquals(5, $config->getRelatedPostsCount());
        $this->assertEquals(20, $config->getTagCloudLimit());
        $this->assertTrue($config->isCacheEnabled());
        $this->assertEquals(3600, $config->getCacheTtl());
    }

    /** @test */
    public function it_can_override_configuration_from_config_file(): void
    {
        Config::set('cms-blog-system.route_prefix', 'articles');
        Config::set('cms-blog-system.pagination.posts_per_page', 15);
        Config::set('cms-blog-system.cache.enabled', false);

        $config = new BlogConfig;

        $this->assertEquals('articles', $config->getRoutePrefix());
        $this->assertEquals(15, $config->getPostsPerPage());
        $this->assertFalse($config->isCacheEnabled());
    }

    /** @test */
    public function it_validates_posts_per_page_range(): void
    {
        Config::set('cms-blog-system.pagination.posts_per_page', 5);
        $config = new BlogConfig;
        $this->assertEquals(5, $config->getPostsPerPage());

        Config::set('cms-blog-system.pagination.posts_per_page', 100);
        $config = new BlogConfig;
        $this->assertEquals(100, $config->getPostsPerPage());

        // Test invalid values default to minimum
        Config::set('cms-blog-system.pagination.posts_per_page', 0);
        $config = new BlogConfig;
        $this->assertEquals(5, $config->getPostsPerPage());

        Config::set('cms-blog-system.pagination.posts_per_page', 101);
        $config = new BlogConfig;
        $this->assertEquals(100, $config->getPostsPerPage());
    }

    /** @test */
    public function it_validates_cache_ttl_range(): void
    {
        Config::set('cms-blog-system.cache.ttl', 300);
        $config = new BlogConfig;
        $this->assertEquals(300, $config->getCacheTtl());

        Config::set('cms-blog-system.cache.ttl', 86400);
        $config = new BlogConfig;
        $this->assertEquals(86400, $config->getCacheTtl());

        // Test invalid values default to reasonable limits
        Config::set('cms-blog-system.cache.ttl', 59);
        $config = new BlogConfig;
        $this->assertEquals(300, $config->getCacheTtl());

        Config::set('cms-blog-system.cache.ttl', 86401);
        $config = new BlogConfig;
        $this->assertEquals(86400, $config->getCacheTtl());
    }

    /** @test */
    public function it_can_get_theme_configuration(): void
    {
        Config::set('cms-blog-system.theme.name', 'custom-theme');
        Config::set('cms-blog-system.theme.layout', 'custom.layout');

        $config = new BlogConfig;

        $this->assertEquals('custom-theme', $config->getThemeName());
        $this->assertEquals('custom.layout', $config->getThemeLayout());
    }

    /** @test */
    public function it_can_get_seo_configuration(): void
    {
        Config::set('cms-blog-system.seo.meta_title_suffix', ' | My Blog');
        Config::set('cms-blog-system.seo.generate_sitemap', true);
        Config::set('cms-blog-system.seo.sitemap_frequency', 'weekly');

        $config = new BlogConfig;

        $this->assertEquals(' | My Blog', $config->getMetaTitleSuffix());
        $this->assertTrue($config->shouldGenerateSitemap());
        $this->assertEquals('weekly', $config->getSitemapFrequency());
    }

    /** @test */
    public function it_can_get_media_configuration(): void
    {
        Config::set('cms-blog-system.media.disk', 's3');
        Config::set('cms-blog-system.media.featured_image_sizes', [
            'thumb' => [300, 200],
            'medium' => [600, 400],
            'large' => [1200, 800],
        ]);

        $config = new BlogConfig;

        $this->assertEquals('s3', $config->getMediaDisk());
        $this->assertEquals([
            'thumb' => [300, 200],
            'medium' => [600, 400],
            'large' => [1200, 800],
        ], $config->getFeaturedImageSizes());
    }

    /** @test */
    public function it_can_get_search_configuration(): void
    {
        Config::set('cms-blog-system.search.driver', 'algolia');
        Config::set('cms-blog-system.search.min_query_length', 3);
        Config::set('cms-blog-system.search.max_results', 50);

        $config = new BlogConfig;

        $this->assertEquals('algolia', $config->getSearchDriver());
        $this->assertEquals(3, $config->getMinQueryLength());
        $this->assertEquals(50, $config->getMaxSearchResults());
    }

    /** @test */
    public function it_can_get_feed_configuration(): void
    {
        Config::set('cms-blog-system.feeds.enabled', true);
        Config::set('cms-blog-system.feeds.title', 'My Blog Feed');
        Config::set('cms-blog-system.feeds.description', 'Latest posts from my blog');
        Config::set('cms-blog-system.feeds.items_count', 20);

        $config = new BlogConfig;

        $this->assertTrue($config->areFeedsEnabled());
        $this->assertEquals('My Blog Feed', $config->getFeedTitle());
        $this->assertEquals('Latest posts from my blog', $config->getFeedDescription());
        $this->assertEquals(20, $config->getFeedItemsCount());
    }

    /** @test */
    public function it_can_get_all_configuration_as_array(): void
    {
        $config = new BlogConfig;
        $allConfig = $config->toArray();

        $this->assertIsArray($allConfig);
        $this->assertArrayHasKey('route_prefix', $allConfig);
        $this->assertArrayHasKey('pagination', $allConfig);
        $this->assertArrayHasKey('cache', $allConfig);
        $this->assertArrayHasKey('theme', $allConfig);
        $this->assertArrayHasKey('seo', $allConfig);
        $this->assertArrayHasKey('media', $allConfig);
        $this->assertArrayHasKey('search', $allConfig);
        $this->assertArrayHasKey('feeds', $allConfig);
    }

    /** @test */
    public function it_can_validate_route_prefix(): void
    {
        Config::set('cms-blog-system.route_prefix', 'valid-prefix');
        $config = new BlogConfig;
        $this->assertEquals('valid-prefix', $config->getRoutePrefix());

        // Test invalid characters are sanitized
        Config::set('cms-blog-system.route_prefix', 'invalid prefix!');
        $config = new BlogConfig;
        $this->assertEquals('invalid-prefix', $config->getRoutePrefix());

        // Test empty prefix defaults to 'blog'
        Config::set('cms-blog-system.route_prefix', '');
        $config = new BlogConfig;
        $this->assertEquals('blog', $config->getRoutePrefix());
    }

    /** @test */
    public function it_can_check_feature_flags(): void
    {
        Config::set('cms-blog-system.features.comments_enabled', true);
        Config::set('cms-blog-system.features.social_sharing', false);
        Config::set('cms-blog-system.features.reading_time', true);

        $config = new BlogConfig;

        $this->assertTrue($config->isFeatureEnabled('comments_enabled'));
        $this->assertFalse($config->isFeatureEnabled('social_sharing'));
        $this->assertTrue($config->isFeatureEnabled('reading_time'));
        $this->assertFalse($config->isFeatureEnabled('non_existent_feature'));
    }

    /** @test */
    public function it_can_get_middleware_configuration(): void
    {
        Config::set('cms-blog-system.middleware.web', ['web', 'auth']);
        Config::set('cms-blog-system.middleware.api', ['api', 'throttle:60,1']);

        $config = new BlogConfig;

        $this->assertEquals(['web', 'auth'], $config->getWebMiddleware());
        $this->assertEquals(['api', 'throttle:60,1'], $config->getApiMiddleware());
    }
}
