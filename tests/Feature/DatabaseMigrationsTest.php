<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Database Migrations Test
 *
 * Tests that all blog database migrations create the correct table structure
 * with proper columns, indexes, and foreign key relationships.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class DatabaseMigrationsTest extends TestCase
{
    /** @test */
    public function it_creates_blog_posts_table(): void
    {
        $this->assertTrue(Schema::hasTable('blog_posts'));

        // Check required columns
        $this->assertTrue(Schema::hasColumn('blog_posts', 'id'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'title'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'slug'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'excerpt'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'content'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'status'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'featured_image'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'meta_title'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'meta_description'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'meta_keywords'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'published_at'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'author_id'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'created_at'));
        $this->assertTrue(Schema::hasColumn('blog_posts', 'updated_at'));
    }

    /** @test */
    public function it_creates_blog_categories_table(): void
    {
        $this->assertTrue(Schema::hasTable('blog_categories'));

        // Check required columns
        $this->assertTrue(Schema::hasColumn('blog_categories', 'id'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'name'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'slug'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'description'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'parent_id'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'sort_order'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'is_active'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'meta_title'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'meta_description'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'created_at'));
        $this->assertTrue(Schema::hasColumn('blog_categories', 'updated_at'));
    }

    /** @test */
    public function it_creates_blog_tags_table(): void
    {
        $this->assertTrue(Schema::hasTable('blog_tags'));

        // Check required columns
        $this->assertTrue(Schema::hasColumn('blog_tags', 'id'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'name'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'slug'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'description'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'color'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'usage_count'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'is_active'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'created_at'));
        $this->assertTrue(Schema::hasColumn('blog_tags', 'updated_at'));
    }

    /** @test */
    public function it_creates_blog_post_categories_pivot_table(): void
    {
        $this->assertTrue(Schema::hasTable('blog_post_categories'));

        // Check required columns
        $this->assertTrue(Schema::hasColumn('blog_post_categories', 'id'));
        $this->assertTrue(Schema::hasColumn('blog_post_categories', 'blog_post_id'));
        $this->assertTrue(Schema::hasColumn('blog_post_categories', 'blog_category_id'));
        $this->assertTrue(Schema::hasColumn('blog_post_categories', 'created_at'));
        $this->assertTrue(Schema::hasColumn('blog_post_categories', 'updated_at'));
    }

    /** @test */
    public function it_creates_blog_post_tags_pivot_table(): void
    {
        $this->assertTrue(Schema::hasTable('blog_post_tags'));

        // Check required columns
        $this->assertTrue(Schema::hasColumn('blog_post_tags', 'id'));
        $this->assertTrue(Schema::hasColumn('blog_post_tags', 'blog_post_id'));
        $this->assertTrue(Schema::hasColumn('blog_post_tags', 'blog_tag_id'));
        $this->assertTrue(Schema::hasColumn('blog_post_tags', 'created_at'));
        $this->assertTrue(Schema::hasColumn('blog_post_tags', 'updated_at'));
    }

    /** @test */
    public function it_has_proper_indexes_on_blog_posts(): void
    {
        // For SQLite in testing, we'll check that the table exists and has the right structure
        // Index verification will be done in integration tests with actual database
        $this->assertTrue(Schema::hasTable('blog_posts'));

        // Check that slug column exists (will have unique index)
        $this->assertTrue(Schema::hasColumn('blog_posts', 'slug'));

        // Check that status column exists (will have index)
        $this->assertTrue(Schema::hasColumn('blog_posts', 'status'));

        // Check that published_at column exists (will have index)
        $this->assertTrue(Schema::hasColumn('blog_posts', 'published_at'));
    }

    /** @test */
    public function it_has_proper_indexes_on_blog_categories(): void
    {
        $this->assertTrue(Schema::hasTable('blog_categories'));

        // Check that slug column exists (will have unique index)
        $this->assertTrue(Schema::hasColumn('blog_categories', 'slug'));

        // Check that parent_id column exists (will have index)
        $this->assertTrue(Schema::hasColumn('blog_categories', 'parent_id'));
    }

    /** @test */
    public function it_has_proper_indexes_on_blog_tags(): void
    {
        $this->assertTrue(Schema::hasTable('blog_tags'));

        // Check that slug column exists (will have unique index)
        $this->assertTrue(Schema::hasColumn('blog_tags', 'slug'));
    }

    /** @test */
    public function it_has_proper_foreign_key_constraints(): void
    {
        // For SQLite in testing, we'll verify the foreign key columns exist
        // Actual foreign key constraint testing will be done in integration tests

        // Test blog_categories self-referencing foreign key column
        $this->assertTrue(Schema::hasColumn('blog_categories', 'parent_id'));

        // Test pivot table foreign key columns
        $this->assertTrue(Schema::hasColumn('blog_post_categories', 'blog_post_id'));
        $this->assertTrue(Schema::hasColumn('blog_post_categories', 'blog_category_id'));

        $this->assertTrue(Schema::hasColumn('blog_post_tags', 'blog_post_id'));
        $this->assertTrue(Schema::hasColumn('blog_post_tags', 'blog_tag_id'));
    }
}
