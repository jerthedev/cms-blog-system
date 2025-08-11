<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Blog Model Relationships Test
 *
 * Tests the relationships between BlogPost, BlogCategory, and BlogTag models.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function blog_post_can_belong_to_many_categories(): void
    {
        $post = BlogPost::factory()->create();
        $category1 = BlogCategory::factory()->create(['name' => 'Technology']);
        $category2 = BlogCategory::factory()->create(['name' => 'Programming']);

        $post->categories()->attach([$category1->id, $category2->id]);

        $this->assertCount(2, $post->categories);
        $this->assertTrue($post->categories->contains($category1));
        $this->assertTrue($post->categories->contains($category2));
    }

    /** @test */
    public function blog_post_can_belong_to_many_tags(): void
    {
        $post = BlogPost::factory()->create();
        $tag1 = BlogTag::factory()->create(['name' => 'PHP']);
        $tag2 = BlogTag::factory()->create(['name' => 'Laravel']);

        $post->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertCount(2, $post->tags);
        $this->assertTrue($post->tags->contains($tag1));
        $this->assertTrue($post->tags->contains($tag2));
    }

    /** @test */
    public function blog_category_can_have_many_posts(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Technology']);
        $post1 = BlogPost::factory()->create(['title' => 'Post 1']);
        $post2 = BlogPost::factory()->create(['title' => 'Post 2']);

        $post1->categories()->attach($category->id);
        $post2->categories()->attach($category->id);

        $this->assertCount(2, $category->posts);
        $this->assertTrue($category->posts->contains($post1));
        $this->assertTrue($category->posts->contains($post2));
    }

    /** @test */
    public function blog_tag_can_have_many_posts(): void
    {
        $tag = BlogTag::factory()->create(['name' => 'PHP']);
        $post1 = BlogPost::factory()->create(['title' => 'Post 1']);
        $post2 = BlogPost::factory()->create(['title' => 'Post 2']);

        $post1->tags()->attach($tag->id);
        $post2->tags()->attach($tag->id);

        $this->assertCount(2, $tag->posts);
        $this->assertTrue($tag->posts->contains($post1));
        $this->assertTrue($tag->posts->contains($post2));
    }

    /** @test */
    public function attaching_tags_increments_usage_count(): void
    {
        $post = BlogPost::factory()->create();
        $tag = BlogTag::factory()->create(['usage_count' => 0]);

        $post->attachTag($tag->id);

        $this->assertEquals(1, $tag->fresh()->usage_count);
    }

    /** @test */
    public function detaching_tags_decrements_usage_count(): void
    {
        $post = BlogPost::factory()->create();
        $tag = BlogTag::factory()->create(['usage_count' => 5]);

        $post->attachTag($tag->id);
        $post->detachTag($tag->id);

        $this->assertEquals(5, $tag->fresh()->usage_count); // Should remain 5 since we attached then detached
    }

    /** @test */
    public function syncing_tags_updates_usage_counts_correctly(): void
    {
        $post = BlogPost::factory()->create();
        $tag1 = BlogTag::factory()->create(['usage_count' => 0]);
        $tag2 = BlogTag::factory()->create(['usage_count' => 0]);
        $tag3 = BlogTag::factory()->create(['usage_count' => 0]);

        // Initial sync
        $post->syncTags([$tag1->id, $tag2->id]);

        $this->assertEquals(1, $tag1->fresh()->usage_count);
        $this->assertEquals(1, $tag2->fresh()->usage_count);
        $this->assertEquals(0, $tag3->fresh()->usage_count);

        // Sync with different tags
        $post->syncTags([$tag2->id, $tag3->id]);

        $this->assertEquals(0, $tag1->fresh()->usage_count); // Removed
        $this->assertEquals(1, $tag2->fresh()->usage_count); // Kept
        $this->assertEquals(1, $tag3->fresh()->usage_count); // Added
    }

    /** @test */
    public function blog_post_can_be_filtered_by_category(): void
    {
        $category1 = BlogCategory::factory()->create(['name' => 'Technology']);
        $category2 = BlogCategory::factory()->create(['name' => 'Business']);

        $post1 = BlogPost::factory()->published()->create(['title' => 'Tech Post']);
        $post2 = BlogPost::factory()->published()->create(['title' => 'Business Post']);
        $post3 = BlogPost::factory()->published()->create(['title' => 'General Post']);

        $post1->categories()->attach($category1->id);
        $post2->categories()->attach($category2->id);

        $techPosts = BlogPost::inCategory($category1->id)->get();
        $businessPosts = BlogPost::inCategory($category2->id)->get();

        $this->assertCount(1, $techPosts);
        $this->assertTrue($techPosts->contains($post1));

        $this->assertCount(1, $businessPosts);
        $this->assertTrue($businessPosts->contains($post2));
    }

    /** @test */
    public function blog_post_can_be_filtered_by_category_slug(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
        $post = BlogPost::factory()->published()->create();

        $post->categories()->attach($category->id);

        $posts = BlogPost::inCategorySlug('technology')->get();

        $this->assertCount(1, $posts);
        $this->assertTrue($posts->contains($post));
    }

    /** @test */
    public function blog_post_can_be_filtered_by_tag(): void
    {
        $tag1 = BlogTag::factory()->create(['name' => 'PHP']);
        $tag2 = BlogTag::factory()->create(['name' => 'JavaScript']);

        $post1 = BlogPost::factory()->published()->create(['title' => 'PHP Post']);
        $post2 = BlogPost::factory()->published()->create(['title' => 'JS Post']);
        $post3 = BlogPost::factory()->published()->create(['title' => 'General Post']);

        $post1->tags()->attach($tag1->id);
        $post2->tags()->attach($tag2->id);

        $phpPosts = BlogPost::withTag($tag1->id)->get();
        $jsPosts = BlogPost::withTag($tag2->id)->get();

        $this->assertCount(1, $phpPosts);
        $this->assertTrue($phpPosts->contains($post1));

        $this->assertCount(1, $jsPosts);
        $this->assertTrue($jsPosts->contains($post2));
    }

    /** @test */
    public function blog_post_can_be_filtered_by_tag_slug(): void
    {
        $tag = BlogTag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
        $post = BlogPost::factory()->published()->create();

        $post->tags()->attach($tag->id);

        $posts = BlogPost::withTagSlug('php')->get();

        $this->assertCount(1, $posts);
        $this->assertTrue($posts->contains($post));
    }

    /** @test */
    public function blog_post_can_be_filtered_by_multiple_tags(): void
    {
        $tag1 = BlogTag::factory()->create(['name' => 'PHP']);
        $tag2 = BlogTag::factory()->create(['name' => 'Laravel']);
        $tag3 = BlogTag::factory()->create(['name' => 'JavaScript']);

        $post1 = BlogPost::factory()->published()->create(['title' => 'PHP Laravel Post']);
        $post2 = BlogPost::factory()->published()->create(['title' => 'PHP Only Post']);
        $post3 = BlogPost::factory()->published()->create(['title' => 'JS Post']);

        $post1->tags()->attach([$tag1->id, $tag2->id]);
        $post2->tags()->attach([$tag1->id]);
        $post3->tags()->attach([$tag3->id]);

        $postsWithBothTags = BlogPost::withAllTags([$tag1->id, $tag2->id])->get();
        $postsWithAnyTag = BlogPost::withAnyTags([$tag1->id, $tag3->id])->get();

        $this->assertCount(1, $postsWithBothTags);
        $this->assertTrue($postsWithBothTags->contains($post1));

        $this->assertCount(3, $postsWithAnyTag);
        $this->assertTrue($postsWithAnyTag->contains($post1));
        $this->assertTrue($postsWithAnyTag->contains($post2));
        $this->assertTrue($postsWithAnyTag->contains($post3));
    }

    /** @test */
    public function relationships_prevent_n_plus_1_queries(): void
    {
        $category = BlogCategory::factory()->create();
        $tags = BlogTag::factory()->count(3)->create();

        $posts = BlogPost::factory()->count(5)->published()->create();

        foreach ($posts as $post) {
            $post->categories()->attach($category->id);
            $post->tags()->attach($tags->pluck('id')->toArray());
        }

        // Test eager loading prevents N+1
        $postsWithRelations = BlogPost::with(['categories', 'tags'])->get();

        $this->assertCount(5, $postsWithRelations);

        // Each post should have the category and tags loaded
        foreach ($postsWithRelations as $post) {
            $this->assertTrue($post->relationLoaded('categories'));
            $this->assertTrue($post->relationLoaded('tags'));
            $this->assertCount(1, $post->categories);
            $this->assertCount(3, $post->tags);
        }
    }

    /** @test */
    public function deleting_post_removes_pivot_relationships(): void
    {
        // Enable media library for this test
        $this->enableMediaLibraryTesting();
        $post = BlogPost::factory()->create();
        $category = BlogCategory::factory()->create();
        $tag = BlogTag::factory()->create(['usage_count' => 0]);

        $post->categories()->attach($category->id);
        $post->attachTag($tag->id);

        $this->assertDatabaseHas('blog_post_categories', [
            'blog_post_id' => $post->id,
            'blog_category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('blog_post_tags', [
            'blog_post_id' => $post->id,
            'blog_tag_id' => $tag->id,
        ]);

        $post->delete();

        $this->assertDatabaseMissing('blog_post_categories', [
            'blog_post_id' => $post->id,
            'blog_category_id' => $category->id,
        ]);

        $this->assertDatabaseMissing('blog_post_tags', [
            'blog_post_id' => $post->id,
            'blog_tag_id' => $tag->id,
        ]);

        // Tag usage count should be decremented
        $this->assertEquals(0, $tag->fresh()->usage_count);
    }

    /** @test */
    public function deleting_category_removes_pivot_relationships(): void
    {
        $post = BlogPost::factory()->create();
        $category = BlogCategory::factory()->create();

        $post->categories()->attach($category->id);

        $this->assertDatabaseHas('blog_post_categories', [
            'blog_post_id' => $post->id,
            'blog_category_id' => $category->id,
        ]);

        $category->delete();

        $this->assertDatabaseMissing('blog_post_categories', [
            'blog_post_id' => $post->id,
            'blog_category_id' => $category->id,
        ]);

        // Post should still exist
        $this->assertDatabaseHas('blog_posts', ['id' => $post->id]);
    }

    /** @test */
    public function deleting_tag_removes_pivot_relationships(): void
    {
        $post = BlogPost::factory()->create();
        $tag = BlogTag::factory()->create();

        $post->tags()->attach($tag->id);

        $this->assertDatabaseHas('blog_post_tags', [
            'blog_post_id' => $post->id,
            'blog_tag_id' => $tag->id,
        ]);

        $tag->delete();

        $this->assertDatabaseMissing('blog_post_tags', [
            'blog_post_id' => $post->id,
            'blog_tag_id' => $tag->id,
        ]);

        // Post should still exist
        $this->assertDatabaseHas('blog_posts', ['id' => $post->id]);
    }
}
