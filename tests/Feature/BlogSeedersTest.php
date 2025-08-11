<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Blog Seeders Test
 *
 * Tests the blog seeder functionality.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogSeedersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_seed_default_blog_categories(): void
    {
        // Ensure no categories exist initially
        $this->assertEquals(0, BlogCategory::count());

        // Run the categories seeder
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogCategoriesSeeder',
        ]);

        // Check that categories were created
        $this->assertGreaterThan(0, BlogCategory::count());

        // Check for expected default categories
        $expectedCategories = [
            'Technology',
            'Web Development',
            'Laravel',
            'PHP',
            'JavaScript',
            'News',
            'Tutorials',
            'General',
        ];

        foreach ($expectedCategories as $categoryName) {
            $this->assertDatabaseHas('blog_categories', [
                'name' => $categoryName,
            ]);
        }
    }

    /** @test */
    public function it_can_seed_default_blog_tags(): void
    {
        // Ensure no tags exist initially
        $this->assertEquals(0, BlogTag::count());

        // Run the tags seeder
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogTagsSeeder',
        ]);

        // Check that tags were created
        $this->assertGreaterThan(0, BlogTag::count());

        // Check for expected default tags
        $expectedTags = [
            'php',
            'laravel',
            'javascript',
            'web-development',
            'tutorial',
            'beginner',
            'advanced',
            'tips',
            'best-practices',
            'framework',
        ];

        foreach ($expectedTags as $tagSlug) {
            $this->assertDatabaseHas('blog_tags', [
                'slug' => $tagSlug,
            ]);
        }
    }

    /** @test */
    public function it_can_seed_sample_blog_posts(): void
    {
        // Seed categories and tags first
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogCategoriesSeeder',
        ]);

        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogTagsSeeder',
        ]);

        // Ensure no posts exist initially
        $this->assertEquals(0, BlogPost::count());

        // Run the posts seeder
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogPostsSeeder',
        ]);

        // Check that posts were created
        $this->assertGreaterThan(0, BlogPost::count());

        // Check that posts have proper attributes
        $posts = BlogPost::all();

        foreach ($posts as $post) {
            $this->assertNotEmpty($post->title);
            $this->assertNotEmpty($post->slug);
            $this->assertNotEmpty($post->content);
            $this->assertContains($post->status, ['published', 'draft']);
            $this->assertNotNull($post->created_at);
            $this->assertNotNull($post->updated_at);
        }
    }

    /** @test */
    public function it_can_run_complete_blog_seeder(): void
    {
        // Ensure database is empty
        $this->assertEquals(0, BlogCategory::count());
        $this->assertEquals(0, BlogTag::count());
        $this->assertEquals(0, BlogPost::count());

        // Run the complete blog seeder
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogSeeder',
        ]);

        // Check that all data was created
        $this->assertGreaterThan(0, BlogCategory::count());
        $this->assertGreaterThan(0, BlogTag::count());
        $this->assertGreaterThan(0, BlogPost::count());
    }

    /** @test */
    public function seeders_dont_duplicate_existing_data(): void
    {
        // Create some existing data
        BlogCategory::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
        BlogTag::factory()->create(['name' => 'PHP', 'slug' => 'php']);

        $initialCategoryCount = BlogCategory::count();
        $initialTagCount = BlogTag::count();

        // Run seeders
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogCategoriesSeeder',
        ]);

        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogTagsSeeder',
        ]);

        // Check that existing data wasn't duplicated
        $this->assertEquals(1, BlogCategory::where('slug', 'technology')->count());
        $this->assertEquals(1, BlogTag::where('slug', 'php')->count());

        // But new data was added
        $this->assertGreaterThan($initialCategoryCount, BlogCategory::count());
        $this->assertGreaterThan($initialTagCount, BlogTag::count());
    }

    /** @test */
    public function seeded_posts_have_relationships(): void
    {
        // Run all seeders
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogSeeder',
        ]);

        $posts = BlogPost::with(['categories', 'tags'])->get();

        // Check that posts have categories and tags
        foreach ($posts as $post) {
            $this->assertGreaterThan(0, $post->categories->count(), 'Post should have categories');
            $this->assertGreaterThan(0, $post->tags->count(), 'Post should have tags');
        }

        // Check that tag usage counts were updated
        $tags = BlogTag::where('usage_count', '>', 0)->get();
        $this->assertGreaterThan(0, $tags->count(), 'Some tags should have usage count > 0');
    }

    /** @test */
    public function seeded_categories_have_proper_hierarchy(): void
    {
        // Run categories seeder
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogCategoriesSeeder',
        ]);

        // Check for hierarchical structure
        $webDev = BlogCategory::where('slug', 'web-development')->first();
        $this->assertNotNull($webDev);

        // Check for child categories
        $children = BlogCategory::where('parent_id', $webDev->id)->get();
        $this->assertGreaterThan(0, $children->count(), 'Web Development should have child categories');

        // Verify hierarchy methods work
        foreach ($children as $child) {
            $this->assertFalse($child->isRoot());
            $this->assertEquals($webDev->id, $child->parent_id);
        }
    }

    /** @test */
    public function seeded_posts_have_realistic_content(): void
    {
        // Run posts seeder
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogPostsSeeder',
        ]);

        $posts = BlogPost::all();

        foreach ($posts as $post) {
            // Check content length (should be substantial)
            $this->assertGreaterThan(200, strlen($post->content), 'Post content should be substantial');

            // Check for markdown formatting
            $this->assertStringContainsString('#', $post->content, 'Content should contain markdown headers');

            // Check meta fields
            if ($post->meta_title) {
                $this->assertLessThanOrEqual(60, strlen($post->meta_title), 'Meta title should be <= 60 chars');
            }

            if ($post->meta_description) {
                $this->assertLessThanOrEqual(160, strlen($post->meta_description), 'Meta description should be <= 160 chars');
            }
        }
    }

    /** @test */
    public function seeders_can_be_run_multiple_times_safely(): void
    {
        // Run seeders first time
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogSeeder',
        ]);

        $firstRunCategoryCount = BlogCategory::count();
        $firstRunTagCount = BlogTag::count();
        $firstRunPostCount = BlogPost::count();

        // Run seeders second time
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogSeeder',
        ]);

        // Counts should not have doubled (no duplicates)
        $this->assertEquals($firstRunCategoryCount, BlogCategory::count());
        $this->assertEquals($firstRunTagCount, BlogTag::count());
        $this->assertEquals($firstRunPostCount, BlogPost::count());
    }

    /** @test */
    public function seeded_data_has_proper_timestamps(): void
    {
        // Run seeders
        Artisan::call('db:seed', [
            '--class' => 'JTD\\CMSBlogSystem\\Database\\Seeders\\BlogSeeder',
        ]);

        // Check categories
        $categories = BlogCategory::all();
        foreach ($categories as $category) {
            $this->assertNotNull($category->created_at);
            $this->assertNotNull($category->updated_at);
        }

        // Check tags
        $tags = BlogTag::all();
        foreach ($tags as $tag) {
            $this->assertNotNull($tag->created_at);
            $this->assertNotNull($tag->updated_at);
        }

        // Check posts
        $posts = BlogPost::all();
        foreach ($posts as $post) {
            $this->assertNotNull($post->created_at);
            $this->assertNotNull($post->updated_at);

            if ($post->status === 'published') {
                $this->assertNotNull($post->published_at);
            }
        }
    }
}
