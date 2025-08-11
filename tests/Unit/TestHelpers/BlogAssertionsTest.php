<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\TestHelpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;
use JTD\CMSBlogSystem\Tests\TestHelpers\BlogAssertions;

/**
 * Blog Assertions Test
 *
 * Tests the BlogAssertions utility class.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogAssertionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_assert_blog_post_status(): void
    {
        $post = BlogPost::factory()->create(['status' => 'published']);

        // Should not throw exception
        BlogAssertions::assertBlogPostStatus('published', $post);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_wrong_blog_post_status(): void
    {
        $post = BlogPost::factory()->create(['status' => 'draft']);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage("Expected blog post status to be 'published', got 'draft'");

        BlogAssertions::assertBlogPostStatus('published', $post);
    }

    /** @test */
    public function it_can_assert_blog_post_is_published(): void
    {
        $post = BlogPost::factory()->published()->create();

        // Should not throw exception
        BlogAssertions::assertBlogPostIsPublished($post);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_non_published_post(): void
    {
        $post = BlogPost::factory()->draft()->create();

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Expected blog post to be published');

        BlogAssertions::assertBlogPostIsPublished($post);
    }

    /** @test */
    public function it_can_assert_blog_post_is_draft(): void
    {
        $post = BlogPost::factory()->draft()->create();

        // Should not throw exception
        BlogAssertions::assertBlogPostIsDraft($post);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_assert_blog_post_is_scheduled(): void
    {
        $post = BlogPost::factory()->scheduled()->create();

        // Should not throw exception
        BlogAssertions::assertBlogPostIsScheduled($post);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_assert_category_hierarchy(): void
    {
        $parent = BlogCategory::factory()->create();
        $child = BlogCategory::factory()->create(['parent_id' => $parent->id]);

        // Should not throw exception
        BlogAssertions::assertCategoryHierarchy($parent, [
            'is_root' => true,
            'is_leaf' => false,
            'depth' => 0,
        ]);

        BlogAssertions::assertCategoryHierarchy($child, [
            'parent_id' => $parent->id,
            'is_root' => false,
            'is_leaf' => true,
            'depth' => 1,
        ]);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_assert_tag_usage_count(): void
    {
        $tag = BlogTag::factory()->create(['usage_count' => 5]);

        // Should not throw exception
        BlogAssertions::assertTagUsageCount(5, $tag);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_wrong_tag_usage_count(): void
    {
        $tag = BlogTag::factory()->create(['usage_count' => 3]);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Expected tag usage count to be 5, got 3');

        BlogAssertions::assertTagUsageCount(5, $tag);
    }

    /** @test */
    public function it_can_assert_tag_cloud_weights(): void
    {
        $tagCloudData = [
            ['name' => 'PHP', 'weight' => 5, 'usage_count' => 50],
            ['name' => 'JavaScript', 'weight' => 3, 'usage_count' => 25],
            ['name' => 'Laravel', 'weight' => 1, 'usage_count' => 5],
        ];

        // Should not throw exception
        BlogAssertions::assertTagCloudWeights(collect($tagCloudData));

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_invalid_tag_cloud_weights(): void
    {
        $tagCloudData = [
            ['name' => 'PHP', 'weight' => 6, 'usage_count' => 50], // Invalid weight > 5
        ];

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Tag weight should be at most 5');

        BlogAssertions::assertTagCloudWeights(collect($tagCloudData));
    }

    /** @test */
    public function it_can_assert_valid_seo_fields(): void
    {
        $post = BlogPost::factory()->create([
            'meta_title' => 'Short Title',
            'meta_description' => 'A good description that is not too long.',
        ]);

        // Should not throw exception
        BlogAssertions::assertValidSeoFields($post);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_long_meta_title(): void
    {
        $post = BlogPost::factory()->create([
            'meta_title' => str_repeat('A', 61), // Too long
        ]);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Meta title should be 60 characters or less');

        BlogAssertions::assertValidSeoFields($post);
    }

    /** @test */
    public function it_can_assert_unique_slug(): void
    {
        // Should not throw exception for unique slug
        BlogAssertions::assertUniqueSlug('unique-slug', BlogPost::class);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_non_unique_slug(): void
    {
        BlogPost::factory()->create(['slug' => 'existing-slug']);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage("Slug 'existing-slug' should be unique but found 1 existing records");

        BlogAssertions::assertUniqueSlug('existing-slug', BlogPost::class);
    }

    /** @test */
    public function it_can_assert_eager_loaded(): void
    {
        $post = BlogPost::factory()->create();
        $post->load(['categories', 'tags']);

        // Should not throw exception
        BlogAssertions::assertEagerLoaded($post, ['categories', 'tags']);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_assert_fillable_attributes(): void
    {
        $post = new BlogPost;

        // Should not throw exception
        BlogAssertions::assertFillableAttributes($post, ['title', 'content', 'status']);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_assert_model_casts(): void
    {
        $post = new BlogPost;

        // Should not throw exception
        BlogAssertions::assertModelCasts($post, [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ]);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_assert_archive_data_structure(): void
    {
        $archiveData = [
            [
                'year' => 2024,
                'month' => 3,
                'month_name' => 'March',
                'count' => 5,
            ],
            [
                'year' => 2024,
                'month' => 2,
                'month_name' => 'February',
                'count' => 3,
            ],
        ];

        // Should not throw exception
        BlogAssertions::assertArchiveDataStructure($archiveData);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_invalid_archive_data(): void
    {
        $archiveData = [
            [
                'year' => 2024,
                'month' => 13, // Invalid month
                'month_name' => 'Invalid',
                'count' => 5,
            ],
        ];

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Archive month should be 1-12');

        BlogAssertions::assertArchiveDataStructure($archiveData);
    }

    /** @test */
    public function it_can_assert_config_in_range(): void
    {
        // Should not throw exception
        BlogAssertions::assertConfigInRange(10, 5, 15, 'posts_per_page');

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_config_out_of_range(): void
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage("Configuration 'posts_per_page' should be at least 5");

        BlogAssertions::assertConfigInRange(3, 5, 15, 'posts_per_page');
    }

    /** @test */
    public function it_handles_empty_archive_data(): void
    {
        // Should not throw exception for empty archive
        BlogAssertions::assertArchiveDataStructure([]);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_handles_models_without_seo_fields(): void
    {
        $post = BlogPost::factory()->create([
            'meta_title' => null,
            'meta_description' => null,
        ]);

        // Should not throw exception when SEO fields are null
        BlogAssertions::assertValidSeoFields($post);

        $this->assertTrue(true); // Test passed if no exception
    }
}
