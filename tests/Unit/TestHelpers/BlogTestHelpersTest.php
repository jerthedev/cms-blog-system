<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\TestHelpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Tests\TestCase;
use JTD\CMSBlogSystem\Tests\TestHelpers\BlogTestHelpers;

/**
 * Blog Test Helpers Test
 *
 * Tests the BlogTestHelpers utility class.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTestHelpersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_blog_ecosystem(): void
    {
        $ecosystem = BlogTestHelpers::createBlogEcosystem([
            'categories_count' => 2,
            'tags_count' => 5,
            'posts_count' => 8,
            'published_ratio' => 0.75,
            'with_relationships' => true,
        ]);

        // Check structure
        $this->assertArrayHasKey('categories', $ecosystem);
        $this->assertArrayHasKey('root_categories', $ecosystem);
        $this->assertArrayHasKey('child_categories', $ecosystem);
        $this->assertArrayHasKey('tags', $ecosystem);
        $this->assertArrayHasKey('posts', $ecosystem);
        $this->assertArrayHasKey('published_posts', $ecosystem);
        $this->assertArrayHasKey('draft_posts', $ecosystem);

        // Check counts
        $this->assertCount(2, $ecosystem['root_categories']);
        $this->assertCount(4, $ecosystem['child_categories']); // 2 children per root
        $this->assertCount(6, $ecosystem['categories']); // 2 root + 4 children
        $this->assertCount(5, $ecosystem['tags']);
        $this->assertCount(8, $ecosystem['posts']);
        $this->assertCount(6, $ecosystem['published_posts']); // 75% of 8
        $this->assertCount(2, $ecosystem['draft_posts']); // 25% of 8

        // Check relationships exist
        $post = $ecosystem['posts']->first();
        $this->assertGreaterThan(0, $post->categories()->count());
        $this->assertGreaterThan(0, $post->tags()->count());
    }

    /** @test */
    public function it_can_create_full_blog_post(): void
    {
        $post = BlogTestHelpers::createFullBlogPost([
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('published', $post->status);
        $this->assertCount(2, $post->categories);
        $this->assertCount(3, $post->tags);

        // Check that relationships are loaded
        $this->assertTrue($post->relationLoaded('categories'));
        $this->assertTrue($post->relationLoaded('tags'));
    }

    /** @test */
    public function it_can_create_category_hierarchy(): void
    {
        $root = BlogTestHelpers::createCategoryHierarchy(3, 2);

        // Check root category
        $this->assertTrue($root->isRoot());
        $this->assertFalse($root->isLeaf());
        $this->assertEquals(0, $root->getDepth());

        // Check that hierarchy was created
        $this->assertCount(2, $root->children);

        // Check second level
        $child = $root->children->first();
        $this->assertFalse($child->isRoot());
        $this->assertFalse($child->isLeaf());
        $this->assertEquals(1, $child->getDepth());
        $this->assertCount(2, $child->children);

        // Check third level (leaves)
        $grandchild = $child->children->first();
        $this->assertFalse($grandchild->isRoot());
        $this->assertTrue($grandchild->isLeaf());
        $this->assertEquals(2, $grandchild->getDepth());
        $this->assertCount(0, $grandchild->children);
    }

    /** @test */
    public function it_can_create_archive_data(): void
    {
        $posts = BlogTestHelpers::createArchiveData();

        $this->assertGreaterThan(0, $posts->count());

        // Check that posts have different publication dates
        $dates = $posts->pluck('published_at')->map(fn ($date) => $date->format('Y-m'))->unique();
        $this->assertGreaterThan(1, $dates->count());

        // Check that all posts are published
        foreach ($posts as $post) {
            $this->assertEquals('published', $post->status);
            $this->assertNotNull($post->published_at);
        }
    }

    /** @test */
    public function it_can_create_tag_cloud(): void
    {
        $tags = BlogTestHelpers::createTagCloud(15);

        $this->assertCount(15, $tags);

        // Check usage count distribution
        $usageCounts = $tags->pluck('usage_count')->sort();
        $this->assertGreaterThan(0, $usageCounts->min());
        $this->assertLessThanOrEqual(100, $usageCounts->max());

        // Should have varied usage counts
        $this->assertGreaterThan(1, $usageCounts->unique()->count());
    }

    /** @test */
    public function it_can_assert_relationships_loaded(): void
    {
        $post = BlogPost::factory()->create();
        $post->load(['categories', 'tags']);

        // Should not throw exception
        BlogTestHelpers::assertRelationshipsLoaded($post, ['categories', 'tags']);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_unloaded_relationships(): void
    {
        $post = BlogPost::factory()->create();

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage("Relationship 'categories' is not loaded");

        BlogTestHelpers::assertRelationshipsLoaded($post, ['categories']);
    }

    /** @test */
    public function it_can_assert_collection_contains(): void
    {
        $posts = BlogPost::factory()->count(3)->create([
            'status' => 'published',
        ]);

        // Should not throw exception
        BlogTestHelpers::assertCollectionContains($posts, ['status' => 'published']);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_when_collection_does_not_contain(): void
    {
        $posts = BlogPost::factory()->count(3)->create([
            'status' => 'published',
        ]);

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Collection does not contain an item with attributes');

        BlogTestHelpers::assertCollectionContains($posts, ['status' => 'draft']);
    }

    /** @test */
    public function it_can_assert_valid_timestamps(): void
    {
        $post = BlogPost::factory()->create();

        // Should not throw exception
        BlogTestHelpers::assertValidTimestamps($post);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_assert_valid_slug(): void
    {
        // Valid slugs should not throw exception
        BlogTestHelpers::assertValidSlug('valid-slug');
        BlogTestHelpers::assertValidSlug('another-valid-slug-123');
        BlogTestHelpers::assertValidSlug('simple');

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_invalid_slug(): void
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        BlogTestHelpers::assertValidSlug('Invalid Slug With Spaces');
    }

    /** @test */
    public function it_can_get_random_subset(): void
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $subset = BlogTestHelpers::randomSubset($collection, 3, 7);

        $this->assertGreaterThanOrEqual(3, $subset->count());
        $this->assertLessThanOrEqual(7, $subset->count());

        // All items should be from original collection
        foreach ($subset as $item) {
            $this->assertTrue($collection->contains($item));
        }
    }

    /** @test */
    public function it_can_create_realistic_content(): void
    {
        $content = BlogTestHelpers::createRealisticContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('# Blog Post Title', $content);
        $this->assertStringContainsString('## Main Content', $content);
        $this->assertStringContainsString('### Code Example', $content);
        $this->assertStringContainsString('```php', $content);
        $this->assertStringContainsString('## Conclusion', $content);
        $this->assertGreaterThan(400, strlen($content));
    }

    /** @test */
    public function ecosystem_without_relationships_works(): void
    {
        $ecosystem = BlogTestHelpers::createBlogEcosystem([
            'categories_count' => 2,
            'tags_count' => 3,
            'posts_count' => 5,
            'with_relationships' => false,
        ]);

        $post = $ecosystem['posts']->first();
        $this->assertEquals(0, $post->categories()->count());
        $this->assertEquals(0, $post->tags()->count());
    }

    /** @test */
    public function it_creates_proper_published_ratio(): void
    {
        $ecosystem = BlogTestHelpers::createBlogEcosystem([
            'posts_count' => 10,
            'published_ratio' => 0.6,
        ]);

        $this->assertCount(6, $ecosystem['published_posts']);
        $this->assertCount(4, $ecosystem['draft_posts']);
    }
}
