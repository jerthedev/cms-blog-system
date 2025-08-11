<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\TestHelpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;

/**
 * Blog Test Helpers
 *
 * Utility methods for creating test data and assertions in blog tests.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTestHelpers
{
    /**
     * Create a complete blog ecosystem with posts, categories, and tags.
     */
    public static function createBlogEcosystem(array $options = []): array
    {
        $options = array_merge([
            'categories_count' => 3,
            'tags_count' => 8,
            'posts_count' => 10,
            'published_ratio' => 0.7,
            'with_relationships' => true,
        ], $options);

        // Create categories with hierarchy
        $rootCategories = BlogCategory::factory()
            ->count($options['categories_count'])
            ->create();

        $childCategories = collect();
        foreach ($rootCategories as $root) {
            $children = BlogCategory::factory()
                ->count(2)
                ->create(['parent_id' => $root->id]);
            $childCategories = $childCategories->merge($children);
        }

        $allCategories = $rootCategories->merge($childCategories);

        // Create tags with realistic usage
        $tags = BlogTag::factory()
            ->count($options['tags_count'])
            ->create();

        // Create posts with mixed states
        $publishedCount = (int) ($options['posts_count'] * $options['published_ratio']);
        $draftCount = $options['posts_count'] - $publishedCount;

        $publishedPosts = BlogPost::factory()
            ->count($publishedCount)
            ->published()
            ->create();

        $draftPosts = BlogPost::factory()
            ->count($draftCount)
            ->draft()
            ->create();

        $allPosts = $publishedPosts->merge($draftPosts);

        // Create relationships if requested
        if ($options['with_relationships']) {
            foreach ($allPosts as $post) {
                // Attach 1-2 categories
                $postCategories = $allCategories->random(rand(1, 2));
                $post->categories()->attach($postCategories->pluck('id'));

                // Attach 2-4 tags and update usage counts
                $postTags = $tags->random(rand(2, 4));
                foreach ($postTags as $tag) {
                    $post->attachTag($tag->id);
                }
            }
        }

        return [
            'categories' => $allCategories,
            'root_categories' => $rootCategories,
            'child_categories' => $childCategories,
            'tags' => $tags,
            'posts' => $allPosts,
            'published_posts' => $publishedPosts,
            'draft_posts' => $draftPosts,
        ];
    }

    /**
     * Create a blog post with full content and relationships.
     */
    public static function createFullBlogPost(array $attributes = []): BlogPost
    {
        $post = BlogPost::factory()->create($attributes);

        // Add categories
        $categories = BlogCategory::factory()->count(2)->create();
        $post->categories()->attach($categories->pluck('id'));

        // Add tags
        $tags = BlogTag::factory()->count(3)->create();
        foreach ($tags as $tag) {
            $post->attachTag($tag->id);
        }

        return $post->fresh(['categories', 'tags']);
    }

    /**
     * Create a category hierarchy for testing.
     */
    public static function createCategoryHierarchy(int $depth = 3, int $childrenPerLevel = 2): BlogCategory
    {
        $root = BlogCategory::factory()->create(['name' => 'Root Category']);

        if ($depth > 1) {
            static::createCategoryChildren($root, $depth - 1, $childrenPerLevel);
        }

        return $root->fresh();
    }

    /**
     * Recursively create category children.
     */
    protected static function createCategoryChildren(BlogCategory $parent, int $remainingDepth, int $childrenCount): void
    {
        if ($remainingDepth <= 0) {
            return;
        }

        $children = BlogCategory::factory()
            ->count($childrenCount)
            ->create(['parent_id' => $parent->id]);

        if ($remainingDepth > 1) {
            foreach ($children as $child) {
                static::createCategoryChildren($child, $remainingDepth - 1, $childrenCount);
            }
        }
    }

    /**
     * Create posts with specific publication dates for archive testing.
     */
    public static function createArchiveData(): Collection
    {
        $posts = collect();

        // Create posts for different months
        $dates = [
            Carbon::create(2024, 1, 15),
            Carbon::create(2024, 2, 15),
            Carbon::create(2024, 3, 15),
            Carbon::create(2024, 4, 15),
        ];

        foreach ($dates as $date) {
            $monthPosts = BlogPost::factory()
                ->count(rand(2, 5))
                ->published()
                ->create(['published_at' => $date]);

            $posts = $posts->merge($monthPosts);
        }

        return $posts;
    }

    /**
     * Create a tag cloud dataset for testing.
     */
    public static function createTagCloud(int $tagCount = 20): Collection
    {
        $tags = collect();

        // Create tags with varying usage counts
        for ($i = 0; $i < $tagCount; $i++) {
            $usageCount = match (true) {
                $i < 3 => rand(50, 100),  // Very popular
                $i < 8 => rand(20, 49),   // Popular
                $i < 15 => rand(5, 19),   // Moderate
                default => rand(1, 4),    // Low usage
            };

            $tag = BlogTag::factory()->create([
                'usage_count' => $usageCount,
            ]);

            $tags->push($tag);
        }

        return $tags;
    }

    /**
     * Assert that a model has the expected relationships loaded.
     */
    public static function assertRelationshipsLoaded($model, array $relationships): void
    {
        foreach ($relationships as $relationship) {
            if (! $model->relationLoaded($relationship)) {
                throw new \PHPUnit\Framework\AssertionFailedError(
                    "Relationship '{$relationship}' is not loaded on ".get_class($model)
                );
            }
        }
    }

    /**
     * Assert that a collection contains models with specific attributes.
     */
    public static function assertCollectionContains(Collection $collection, array $attributes): void
    {
        $found = $collection->first(function ($item) use ($attributes) {
            foreach ($attributes as $key => $value) {
                if ($value !== $item->$key) {
                    return false;
                }
            }

            return true;
        });

        if (! $found) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                'Collection does not contain an item with attributes: '.json_encode($attributes)
            );
        }
    }

    /**
     * Assert that a model has valid timestamps.
     */
    public static function assertValidTimestamps($model): void
    {
        if (! $model->created_at instanceof Carbon) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                'Model created_at is not a Carbon instance'
            );
        }

        if (! $model->updated_at instanceof Carbon) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                'Model updated_at is not a Carbon instance'
            );
        }

        if ($model->created_at->isAfter($model->updated_at)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                'Model created_at cannot be after updated_at'
            );
        }
    }

    /**
     * Assert that a slug is properly formatted.
     */
    public static function assertValidSlug(string $slug): void
    {
        if (! preg_match('/^[a-z0-9\-]+$/', $slug)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Slug '{$slug}' is not properly formatted (should be lowercase alphanumeric with hyphens)"
            );
        }

        if (str_starts_with($slug, '-') || str_ends_with($slug, '-')) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Slug '{$slug}' should not start or end with a hyphen"
            );
        }

        if (str_contains($slug, '--')) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "Slug '{$slug}' should not contain consecutive hyphens"
            );
        }
    }

    /**
     * Get a random subset of a collection.
     */
    public static function randomSubset(Collection $collection, int $min = 1, ?int $max = null): Collection
    {
        $max = $max ?? $collection->count();
        $count = rand($min, min($max, $collection->count()));

        return $collection->random($count);
    }

    /**
     * Create realistic blog content with markdown.
     */
    public static function createRealisticContent(): string
    {
        $paragraphs = [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
            'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.',
        ];

        $content = "# Blog Post Title\n\n";
        $content .= "This is an introduction paragraph that sets the context for the blog post.\n\n";
        $content .= "## Main Content\n\n";

        $selectedParagraphs = collect($paragraphs)->random(rand(2, 4));
        foreach ($selectedParagraphs as $paragraph) {
            $content .= $paragraph."\n\n";
        }

        $content .= "### Code Example\n\n";
        $content .= "```php\n";
        $content .= "<?php\n";
        $content .= "echo 'Hello, World!';\n";
        $content .= "```\n\n";

        $content .= "## Conclusion\n\n";
        $content .= 'This concludes our blog post with some final thoughts and insights.';

        return $content;
    }
}
