<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Facades;

use Illuminate\Support\Facades\Config;
use JTD\CMSBlogSystem\Facades\BlogConfig;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Blog Configuration Facade Test
 *
 * Tests the BlogConfig facade functionality.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogConfigFacadeTest extends TestCase
{
    /** @test */
    public function it_can_access_configuration_through_facade(): void
    {
        $this->assertEquals('blog', BlogConfig::getRoutePrefix());
        $this->assertEquals(10, BlogConfig::getPostsPerPage());
        $this->assertTrue(BlogConfig::isCacheEnabled());
    }

    /** @test */
    public function facade_respects_configuration_overrides(): void
    {
        Config::set('cms-blog-system.route_prefix', 'articles');
        Config::set('cms-blog-system.pagination.posts_per_page', 15);
        Config::set('cms-blog-system.cache.enabled', false);

        $this->assertEquals('articles', BlogConfig::getRoutePrefix());
        $this->assertEquals(15, BlogConfig::getPostsPerPage());
        $this->assertFalse(BlogConfig::isCacheEnabled());
    }

    /** @test */
    public function facade_can_get_all_configuration_as_array(): void
    {
        $config = BlogConfig::toArray();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('route_prefix', $config);
        $this->assertArrayHasKey('pagination', $config);
        $this->assertArrayHasKey('cache', $config);
    }

    /** @test */
    public function facade_can_check_feature_flags(): void
    {
        Config::set('cms-blog-system.features.comments_enabled', true);
        Config::set('cms-blog-system.features.social_sharing', false);

        $this->assertTrue(BlogConfig::isFeatureEnabled('comments_enabled'));
        $this->assertFalse(BlogConfig::isFeatureEnabled('social_sharing'));
        $this->assertFalse(BlogConfig::isFeatureEnabled('non_existent_feature'));
    }
}
