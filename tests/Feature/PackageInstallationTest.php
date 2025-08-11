<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature;

use JTD\CMSBlogSystem\CMSBlogSystemServiceProvider;
use JTD\CMSBlogSystem\Support\CMSBlogSystem;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Package Installation Test
 *
 * Tests the basic installation and registration of the CMS Blog System package.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PackageInstallationTest extends TestCase
{
    /** @test */
    public function it_registers_the_service_provider(): void
    {
        $this->assertTrue(
            $this->app->providerIsLoaded(CMSBlogSystemServiceProvider::class)
        );
    }

    /** @test */
    public function it_registers_the_main_service(): void
    {
        $this->assertTrue($this->app->bound(CMSBlogSystem::class));
        $this->assertTrue($this->app->bound('cms-blog-system'));

        $service = $this->app->make(CMSBlogSystem::class);
        $this->assertInstanceOf(CMSBlogSystem::class, $service);
    }

    /** @test */
    public function it_loads_configuration(): void
    {
        $this->assertConfigExists('cms-blog-system.route_prefix');
        $this->assertConfigExists('cms-blog-system.pagination.posts_per_page');
        $this->assertConfigExists('cms-blog-system.cache.enabled');
        $this->assertConfigExists('cms-blog-system.seo.generate_sitemap');
        $this->assertConfigExists('cms-blog-system.feeds.enabled');
    }

    /** @test */
    public function it_registers_routes(): void
    {
        $this->assertRouteExists('blog.index');
        $this->assertRouteExists('blog.show');
        $this->assertRouteExists('blog.category');
        $this->assertRouteExists('blog.tag');
        $this->assertRouteExists('blog.rss');
        $this->assertRouteExists('blog.search');
    }

    /** @test */
    public function it_loads_views(): void
    {
        $this->assertViewExists('cms-blog-system::layouts.app');
        $this->assertViewExists('cms-blog-system::partials.header');
        $this->assertViewExists('cms-blog-system::partials.footer');
    }

    /** @test */
    public function it_registers_console_commands(): void
    {
        $this->assertTrue($this->app->runningInConsole());

        $commands = $this->app->make('Illuminate\Contracts\Console\Kernel')->all();
        $this->assertArrayHasKey('blog:install', $commands);
    }

    /** @test */
    public function it_registers_middleware(): void
    {
        $router = $this->app['router'];
        $middleware = $router->getMiddleware();

        $this->assertArrayHasKey('blog.auth', $middleware);
    }

    /** @test */
    public function the_cms_blog_system_helper_works(): void
    {
        $service = app('cms-blog-system');

        $this->assertInstanceOf(CMSBlogSystem::class, $service);
        $this->assertEquals('1.0.0-dev', $service->version());
        $this->assertEquals('bootstrap', $service->framework());
        $this->assertTrue($service->isBootstrap());
        $this->assertFalse($service->isTailwind());
        $this->assertEquals('blog', $service->routePrefix());
        $this->assertTrue($service->seoEnabled());
        $this->assertTrue($service->rssEnabled());
    }

    /** @test */
    public function it_can_switch_to_tailwind_framework(): void
    {
        config(['cms-blog-system.framework' => 'tailwind']);

        $service = app('cms-blog-system');

        $this->assertEquals('tailwind', $service->framework());
        $this->assertFalse($service->isBootstrap());
        $this->assertTrue($service->isTailwind());
    }
}
