<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\TestHelpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use PHPUnit\Framework\Assert;

/**
 * Blog Assertions
 *
 * Custom assertions for blog-specific testing scenarios.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogAssertions extends Assert
{
    /**
     * Assert that a blog post has the expected publishing state.
     */
    public static function assertBlogPostStatus(string $expectedStatus, BlogPost $post, string $message = ''): void
    {
        $actualStatus = $post->status;

        static::assertEquals(
            $expectedStatus,
            $actualStatus,
            $message ?: "Expected blog post status to be '{$expectedStatus}', got '{$actualStatus}'"
        );
    }

    /**
     * Assert that a blog post is published.
     */
    public static function assertBlogPostIsPublished(BlogPost $post, string $message = ''): void
    {
        static::assertEquals(
            'published',
            $post->status,
            $message ?: 'Expected blog post to be published'
        );

        static::assertTrue(
            $post->isPublished(),
            $message ?: 'Expected isPublished() to return true'
        );

        static::assertNotNull(
            $post->published_at,
            $message ?: 'Expected published_at to be set'
        );
    }

    /**
     * Assert that a blog post is a draft.
     */
    public static function assertBlogPostIsDraft(BlogPost $post, string $message = ''): void
    {
        static::assertEquals(
            'draft',
            $post->status,
            $message ?: 'Expected blog post to be draft'
        );

        static::assertFalse(
            $post->isPublished(),
            $message ?: 'Expected isPublished() to return false'
        );
    }

    /**
     * Assert that a blog post is scheduled.
     */
    public static function assertBlogPostIsScheduled(BlogPost $post, string $message = ''): void
    {
        static::assertEquals(
            'scheduled',
            $post->status,
            $message ?: 'Expected blog post to be scheduled'
        );

        static::assertTrue(
            $post->isScheduled(),
            $message ?: 'Expected isScheduled() to return true'
        );

        static::assertNotNull(
            $post->published_at,
            $message ?: 'Expected published_at to be set for scheduled post'
        );

        static::assertTrue(
            $post->published_at->isFuture(),
            $message ?: 'Expected published_at to be in the future for scheduled post'
        );
    }

    /**
     * Assert that a category has the expected hierarchy structure.
     */
    public static function assertCategoryHierarchy(BlogCategory $category, array $expectedStructure, string $message = ''): void
    {
        if (isset($expectedStructure['parent_id'])) {
            static::assertEquals(
                $expectedStructure['parent_id'],
                $category->parent_id,
                $message ?: "Expected category parent_id to be {$expectedStructure['parent_id']}"
            );
        }

        if (isset($expectedStructure['children_count'])) {
            static::assertCount(
                $expectedStructure['children_count'],
                $category->children,
                $message ?: "Expected category to have {$expectedStructure['children_count']} children"
            );
        }

        if (isset($expectedStructure['depth'])) {
            static::assertEquals(
                $expectedStructure['depth'],
                $category->getDepth(),
                $message ?: "Expected category depth to be {$expectedStructure['depth']}"
            );
        }

        if (isset($expectedStructure['is_root'])) {
            static::assertEquals(
                $expectedStructure['is_root'],
                $category->isRoot(),
                $message ?: 'Category root status does not match expected'
            );
        }

        if (isset($expectedStructure['is_leaf'])) {
            static::assertEquals(
                $expectedStructure['is_leaf'],
                $category->isLeaf(),
                $message ?: 'Category leaf status does not match expected'
            );
        }
    }

    /**
     * Assert that a tag has the expected usage count.
     */
    public static function assertTagUsageCount(int $expectedCount, BlogTag $tag, string $message = ''): void
    {
        static::assertEquals(
            $expectedCount,
            $tag->usage_count,
            $message ?: "Expected tag usage count to be {$expectedCount}, got {$tag->usage_count}"
        );
    }

    /**
     * Assert that a tag cloud has proper weight distribution.
     */
    public static function assertTagCloudWeights(Collection $tagCloudData, string $message = ''): void
    {
        static::assertNotEmpty($tagCloudData, $message ?: 'Tag cloud data should not be empty');

        foreach ($tagCloudData as $tagData) {
            static::assertArrayHasKey('weight', $tagData, $message ?: 'Tag cloud item should have weight');
            static::assertIsInt($tagData['weight'], $message ?: 'Tag weight should be integer');
            static::assertGreaterThanOrEqual(1, $tagData['weight'], $message ?: 'Tag weight should be at least 1');
            static::assertLessThanOrEqual(5, $tagData['weight'], $message ?: 'Tag weight should be at most 5');
        }

        // Check that weights are distributed (not all the same)
        $weights = collect($tagCloudData)->pluck('weight')->unique();
        static::assertGreaterThan(1, $weights->count(), $message ?: 'Tag cloud should have varied weights');
    }

    /**
     * Assert that a model has valid SEO fields.
     */
    public static function assertValidSeoFields(Model $model, string $message = ''): void
    {
        if ($model->meta_title) {
            static::assertIsString($model->meta_title, $message ?: 'Meta title should be string');
            static::assertLessThanOrEqual(60, strlen($model->meta_title), $message ?: 'Meta title should be 60 characters or less');
        }

        if ($model->meta_description) {
            static::assertIsString($model->meta_description, $message ?: 'Meta description should be string');
            static::assertLessThanOrEqual(160, strlen($model->meta_description), $message ?: 'Meta description should be 160 characters or less');
        }
    }

    /**
     * Assert that a collection is properly paginated.
     */
    public static function assertPaginatedCollection($paginator, int $expectedPerPage, string $message = ''): void
    {
        static::assertInstanceOf(
            \Illuminate\Contracts\Pagination\LengthAwarePaginator::class,
            $paginator,
            $message ?: 'Expected a paginator instance'
        );

        static::assertEquals(
            $expectedPerPage,
            $paginator->perPage(),
            $message ?: "Expected {$expectedPerPage} items per page"
        );

        static::assertLessThanOrEqual(
            $expectedPerPage,
            $paginator->count(),
            $message ?: "Current page should not exceed {$expectedPerPage} items"
        );
    }

    /**
     * Assert that a slug is unique in the database.
     */
    public static function assertUniqueSlug(string $slug, string $modelClass, ?int $excludeId = null, string $message = ''): void
    {
        $query = $modelClass::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $count = $query->count();

        static::assertEquals(
            0,
            $count,
            $message ?: "Slug '{$slug}' should be unique but found {$count} existing records"
        );
    }

    /**
     * Assert that relationships are properly loaded to prevent N+1 queries.
     */
    public static function assertEagerLoaded(Model $model, array $relationships, string $message = ''): void
    {
        foreach ($relationships as $relationship) {
            static::assertTrue(
                $model->relationLoaded($relationship),
                $message ?: "Relationship '{$relationship}' should be eager loaded on ".get_class($model)
            );
        }
    }

    /**
     * Assert that a model has the expected fillable attributes.
     */
    public static function assertFillableAttributes(Model $model, array $expectedFillable, string $message = ''): void
    {
        $actualFillable = $model->getFillable();

        foreach ($expectedFillable as $attribute) {
            static::assertContains(
                $attribute,
                $actualFillable,
                $message ?: "Attribute '{$attribute}' should be fillable on ".get_class($model)
            );
        }
    }

    /**
     * Assert that a model has the expected casts.
     */
    public static function assertModelCasts(Model $model, array $expectedCasts, string $message = ''): void
    {
        $actualCasts = $model->getCasts();

        foreach ($expectedCasts as $attribute => $expectedCast) {
            static::assertArrayHasKey(
                $attribute,
                $actualCasts,
                $message ?: "Attribute '{$attribute}' should have a cast defined on ".get_class($model)
            );

            static::assertEquals(
                $expectedCast,
                $actualCasts[$attribute],
                $message ?: "Attribute '{$attribute}' should be cast to '{$expectedCast}' on ".get_class($model)
            );
        }
    }

    /**
     * Assert that archive data has the expected structure.
     */
    public static function assertArchiveDataStructure(array $archiveData, string $message = ''): void
    {
        static::assertIsArray($archiveData, $message ?: 'Archive data should be an array');

        if (empty($archiveData)) {
            return; // Empty archive is valid
        }

        foreach ($archiveData as $item) {
            static::assertArrayHasKey('year', $item, $message ?: 'Archive item should have year');
            static::assertArrayHasKey('month', $item, $message ?: 'Archive item should have month');
            static::assertArrayHasKey('month_name', $item, $message ?: 'Archive item should have month_name');
            static::assertArrayHasKey('count', $item, $message ?: 'Archive item should have count');

            static::assertIsInt($item['year'], $message ?: 'Archive year should be integer');
            static::assertIsInt($item['month'], $message ?: 'Archive month should be integer');
            static::assertIsString($item['month_name'], $message ?: 'Archive month_name should be string');
            static::assertIsInt($item['count'], $message ?: 'Archive count should be integer');

            static::assertGreaterThanOrEqual(1, $item['month'], $message ?: 'Archive month should be 1-12');
            static::assertLessThanOrEqual(12, $item['month'], $message ?: 'Archive month should be 1-12');
            static::assertGreaterThan(0, $item['count'], $message ?: 'Archive count should be positive');
        }
    }

    /**
     * Assert that a configuration value is within expected range.
     */
    public static function assertConfigInRange($value, int $min, int $max, string $configKey, string $message = ''): void
    {
        static::assertGreaterThanOrEqual(
            $min,
            $value,
            $message ?: "Configuration '{$configKey}' should be at least {$min}"
        );

        static::assertLessThanOrEqual(
            $max,
            $value,
            $message ?: "Configuration '{$configKey}' should be at most {$max}"
        );
    }
}
