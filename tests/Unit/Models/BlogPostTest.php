<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogPost Model Test
 *
 * Tests the BlogPost model functionality including publishing states,
 * media integration, SEO fields, and slug generation.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_blog_post(): void
    {
        $post = BlogPost::create([
            'title' => 'Test Blog Post',
            'content' => '# Test Content\n\nThis is a test blog post.',
            'status' => 'draft',
        ]);

        $this->assertInstanceOf(BlogPost::class, $post);
        $this->assertEquals('Test Blog Post', $post->title);
        $this->assertEquals('# Test Content\n\nThis is a test blog post.', $post->content);
        $this->assertEquals('draft', $post->status);
        $this->assertNotNull($post->slug);
    }

    /** @test */
    public function it_automatically_generates_slug_from_title(): void
    {
        $post = BlogPost::create([
            'title' => 'This is a Test Blog Post Title',
            'content' => 'Test content',
            'status' => 'draft',
        ]);

        $this->assertEquals('this-is-a-test-blog-post-title', $post->slug);
    }

    /** @test */
    public function it_ensures_slug_uniqueness(): void
    {
        // Create first post
        BlogPost::create([
            'title' => 'Duplicate Title',
            'content' => 'First post content',
            'status' => 'draft',
        ]);

        // Create second post with same title
        $secondPost = BlogPost::create([
            'title' => 'Duplicate Title',
            'content' => 'Second post content',
            'status' => 'draft',
        ]);

        $this->assertEquals('duplicate-title', BlogPost::first()->slug);
        $this->assertStringStartsWith('duplicate-title-', $secondPost->slug);
        $this->assertNotEquals(BlogPost::first()->slug, $secondPost->slug);
    }

    /** @test */
    public function it_can_set_custom_slug(): void
    {
        $post = BlogPost::create([
            'title' => 'Test Post',
            'slug' => 'custom-slug-here',
            'content' => 'Test content',
            'status' => 'draft',
        ]);

        $this->assertEquals('custom-slug-here', $post->slug);
    }

    /** @test */
    public function it_has_correct_publishing_states(): void
    {
        $validStates = ['draft', 'published', 'scheduled', 'archived'];

        foreach ($validStates as $state) {
            $post = BlogPost::create([
                'title' => "Test Post - {$state}",
                'content' => 'Test content',
                'status' => $state,
            ]);

            $this->assertEquals($state, $post->status);
        }
    }

    /** @test */
    public function it_defaults_to_draft_status(): void
    {
        $post = BlogPost::create([
            'title' => 'Test Post',
            'content' => 'Test content',
        ]);

        $this->assertEquals('draft', $post->status);
    }

    /** @test */
    public function it_can_check_if_published(): void
    {
        $draftPost = BlogPost::create([
            'title' => 'Draft Post',
            'content' => 'Test content',
            'status' => 'draft',
        ]);

        $publishedPost = BlogPost::create([
            'title' => 'Published Post',
            'content' => 'Test content',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertFalse($draftPost->isPublished());
        $this->assertTrue($publishedPost->isPublished());
    }

    /** @test */
    public function it_can_check_if_scheduled(): void
    {
        $scheduledPost = BlogPost::create([
            'title' => 'Scheduled Post',
            'content' => 'Test content',
            'status' => 'scheduled',
            'published_at' => now()->addDay(),
        ]);

        $this->assertTrue($scheduledPost->isScheduled());
    }

    /** @test */
    public function it_can_publish_a_post(): void
    {
        $post = BlogPost::create([
            'title' => 'Draft Post',
            'content' => 'Test content',
            'status' => 'draft',
        ]);

        $post->publish();

        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->isToday());
    }

    /** @test */
    public function it_can_schedule_a_post(): void
    {
        $post = BlogPost::create([
            'title' => 'Draft Post',
            'content' => 'Test content',
            'status' => 'draft',
        ]);

        $scheduleDate = now()->addWeek();
        $post->schedule($scheduleDate);

        $this->assertEquals('scheduled', $post->status);
        $this->assertEquals($scheduleDate->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_archive_a_post(): void
    {
        $post = BlogPost::create([
            'title' => 'Published Post',
            'content' => 'Test content',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $post->archive();

        $this->assertEquals('archived', $post->status);
    }

    /** @test */
    public function it_has_seo_fields(): void
    {
        $post = BlogPost::create([
            'title' => 'SEO Test Post',
            'content' => 'Test content',
            'meta_title' => 'Custom SEO Title',
            'meta_description' => 'This is a custom meta description for SEO.',
            'meta_keywords' => 'seo, test, blog, post',
            'status' => 'draft',
        ]);

        $this->assertEquals('Custom SEO Title', $post->meta_title);
        $this->assertEquals('This is a custom meta description for SEO.', $post->meta_description);
        $this->assertEquals('seo, test, blog, post', $post->meta_keywords);
    }

    /** @test */
    public function it_can_generate_excerpt_from_content(): void
    {
        $post = BlogPost::create([
            'title' => 'Test Post',
            'content' => 'This is a very long content that should be truncated when generating an excerpt. It contains multiple sentences and should be cut off at a reasonable length to provide a good preview of the content.',
            'status' => 'draft',
        ]);

        $excerpt = $post->getExcerpt();

        $this->assertNotEmpty($excerpt);
        $this->assertLessThanOrEqual(150, strlen($excerpt));
        $this->assertStringStartsWith('This is a very long content', $excerpt);
    }

    /** @test */
    public function it_uses_custom_excerpt_when_provided(): void
    {
        $customExcerpt = 'This is a custom excerpt for the blog post.';

        $post = BlogPost::create([
            'title' => 'Test Post',
            'content' => 'This is the full content of the blog post.',
            'excerpt' => $customExcerpt,
            'status' => 'draft',
        ]);

        $this->assertEquals($customExcerpt, $post->getExcerpt());
    }

    /** @test */
    public function it_can_scope_published_posts(): void
    {
        // Create posts with different statuses
        BlogPost::create(['title' => 'Draft Post', 'content' => 'Content', 'status' => 'draft']);
        BlogPost::create(['title' => 'Published Post', 'content' => 'Content', 'status' => 'published', 'published_at' => now()]);
        BlogPost::create(['title' => 'Scheduled Post', 'content' => 'Content', 'status' => 'scheduled', 'published_at' => now()->addDay()]);
        BlogPost::create(['title' => 'Archived Post', 'content' => 'Content', 'status' => 'archived']);

        $publishedPosts = BlogPost::published()->get();

        $this->assertCount(1, $publishedPosts);
        $this->assertEquals('Published Post', $publishedPosts->first()->title);
    }

    /** @test */
    public function it_can_scope_draft_posts(): void
    {
        BlogPost::create(['title' => 'Draft Post', 'content' => 'Content', 'status' => 'draft']);
        BlogPost::create(['title' => 'Published Post', 'content' => 'Content', 'status' => 'published', 'published_at' => now()]);

        $draftPosts = BlogPost::draft()->get();

        $this->assertCount(1, $draftPosts);
        $this->assertEquals('Draft Post', $draftPosts->first()->title);
    }

    /** @test */
    public function it_can_scope_scheduled_posts(): void
    {
        BlogPost::create(['title' => 'Draft Post', 'content' => 'Content', 'status' => 'draft']);
        BlogPost::create(['title' => 'Scheduled Post', 'content' => 'Content', 'status' => 'scheduled', 'published_at' => now()->addDay()]);

        $scheduledPosts = BlogPost::scheduled()->get();

        $this->assertCount(1, $scheduledPosts);
        $this->assertEquals('Scheduled Post', $scheduledPosts->first()->title);
    }

    /** @test */
    public function it_has_media_collections(): void
    {
        $post = BlogPost::create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft',
        ]);

        // Test that media collections are available
        $this->assertTrue(method_exists($post, 'getMedia'));
        $this->assertTrue(method_exists($post, 'addMedia'));
    }

    /** @test */
    public function it_can_get_featured_image_url(): void
    {
        $post = BlogPost::create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'featured_image' => '/images/featured/test-image.jpg',
            'status' => 'draft',
        ]);

        $this->assertEquals('/images/featured/test-image.jpg', $post->getFeaturedImageUrl());
    }

    /** @test */
    public function it_returns_null_for_missing_featured_image(): void
    {
        $post = BlogPost::create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft',
        ]);

        $this->assertNull($post->getFeaturedImageUrl());
    }

    /** @test */
    public function it_casts_published_at_to_carbon(): void
    {
        $publishedAt = now();

        $post = BlogPost::create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'published',
            'published_at' => $publishedAt,
        ]);

        $this->assertInstanceOf(Carbon::class, $post->published_at);
        $this->assertEquals($publishedAt->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_has_fillable_attributes(): void
    {
        $fillable = [
            'title', 'slug', 'excerpt', 'content', 'status', 'featured_image',
            'meta_title', 'meta_description', 'meta_keywords', 'published_at', 'author_id',
        ];

        $post = new BlogPost;

        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, $post->getFillable());
        }
    }
}
