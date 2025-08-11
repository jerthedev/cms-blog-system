<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogPost Factory Test
 *
 * Tests the BlogPost factory functionality and states.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPostFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_blog_post_using_factory(): void
    {
        $post = BlogPost::factory()->create();

        $this->assertInstanceOf(BlogPost::class, $post);
        $this->assertNotEmpty($post->title);
        $this->assertNotEmpty($post->slug);
        $this->assertNotEmpty($post->content);
        $this->assertContains($post->status, ['draft', 'published', 'scheduled', 'archived']);
    }

    /** @test */
    public function it_can_create_published_posts(): void
    {
        $post = BlogPost::factory()->published()->create();

        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->isPast());
    }

    /** @test */
    public function it_can_create_draft_posts(): void
    {
        $post = BlogPost::factory()->draft()->create();

        $this->assertEquals('draft', $post->status);
        $this->assertNull($post->published_at);
    }

    /** @test */
    public function it_can_create_scheduled_posts(): void
    {
        $post = BlogPost::factory()->scheduled()->create();

        $this->assertEquals('scheduled', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->isFuture());
    }

    /** @test */
    public function it_can_create_archived_posts(): void
    {
        $post = BlogPost::factory()->archived()->create();

        $this->assertEquals('archived', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->isPast());
    }

    /** @test */
    public function it_can_create_posts_with_seo_fields(): void
    {
        $post = BlogPost::factory()->withSeo()->create();

        $this->assertNotNull($post->meta_title);
        $this->assertNotNull($post->meta_description);
        $this->assertNotNull($post->meta_keywords);
    }

    /** @test */
    public function it_can_create_posts_with_featured_image(): void
    {
        $post = BlogPost::factory()->withFeaturedImage()->create();

        $this->assertNotNull($post->featured_image);
        $this->assertStringContainsString('http', $post->featured_image);
    }

    /** @test */
    public function it_can_create_posts_with_custom_excerpt(): void
    {
        $post = BlogPost::factory()->withExcerpt()->create();

        $this->assertNotNull($post->excerpt);
        $this->assertNotEmpty($post->excerpt);
    }

    /** @test */
    public function it_generates_markdown_content(): void
    {
        $post = BlogPost::factory()->create();

        $this->assertStringContainsString('#', $post->content);
        $this->assertStringContainsString('##', $post->content);
        $this->assertStringContainsString('-', $post->content);
    }

    /** @test */
    public function it_can_create_multiple_posts(): void
    {
        $posts = BlogPost::factory()->count(5)->create();

        $this->assertCount(5, $posts);

        // Ensure all posts have unique slugs
        $slugs = $posts->pluck('slug')->toArray();
        $this->assertCount(5, array_unique($slugs));
    }

    /** @test */
    public function it_can_combine_states(): void
    {
        $post = BlogPost::factory()
            ->published()
            ->withSeo()
            ->withFeaturedImage()
            ->create();

        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->meta_title);
        $this->assertNotNull($post->featured_image);
    }
}
