<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogTag Model Test
 *
 * Tests the BlogTag model functionality including usage tracking,
 * slug generation, color management, and tag cloud calculations.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_blog_tag(): void
    {
        $tag = BlogTag::create([
            'name' => 'Laravel',
            'description' => 'PHP framework for web artisans.',
        ]);

        $this->assertInstanceOf(BlogTag::class, $tag);
        $this->assertEquals('Laravel', $tag->name);
        $this->assertEquals('PHP framework for web artisans.', $tag->description);
        $this->assertNotNull($tag->slug);
        $this->assertTrue($tag->is_active);
        $this->assertEquals(0, $tag->usage_count);
    }

    /** @test */
    public function it_automatically_generates_slug_from_name(): void
    {
        $tag = BlogTag::create([
            'name' => 'Web Development & Design',
            'description' => 'Web development articles.',
        ]);

        $this->assertEquals('web-development-design', $tag->slug);
    }

    /** @test */
    public function it_ensures_slug_uniqueness(): void
    {
        // Create first tag
        BlogTag::create([
            'name' => 'JavaScript',
            'description' => 'First JavaScript tag.',
        ]);

        // Create second tag with same name
        $secondTag = BlogTag::create([
            'name' => 'JavaScript',
            'description' => 'Second JavaScript tag.',
        ]);

        $this->assertEquals('javascript', BlogTag::first()->slug);
        $this->assertStringStartsWith('javascript-', $secondTag->slug);
        $this->assertNotEquals(BlogTag::first()->slug, $secondTag->slug);
    }

    /** @test */
    public function it_can_set_custom_slug(): void
    {
        $tag = BlogTag::create([
            'name' => 'Vue.js',
            'slug' => 'vuejs',
            'description' => 'Vue.js framework.',
        ]);

        $this->assertEquals('vuejs', $tag->slug);
    }

    /** @test */
    public function it_defaults_to_active_status(): void
    {
        $tag = BlogTag::create([
            'name' => 'Test Tag',
            'description' => 'Test description.',
        ]);

        $this->assertTrue($tag->is_active);
    }

    /** @test */
    public function it_can_be_inactive(): void
    {
        $tag = BlogTag::create([
            'name' => 'Inactive Tag',
            'description' => 'Test description.',
            'is_active' => false,
        ]);

        $this->assertFalse($tag->is_active);
    }

    /** @test */
    public function it_defaults_to_zero_usage_count(): void
    {
        $tag = BlogTag::create([
            'name' => 'New Tag',
            'description' => 'Test description.',
        ]);

        $this->assertEquals(0, $tag->usage_count);
    }

    /** @test */
    public function it_can_increment_usage_count(): void
    {
        $tag = BlogTag::create([
            'name' => 'Popular Tag',
            'description' => 'Test description.',
        ]);

        $tag->incrementUsage();

        $this->assertEquals(1, $tag->fresh()->usage_count);
    }

    /** @test */
    public function it_can_decrement_usage_count(): void
    {
        $tag = BlogTag::create([
            'name' => 'Used Tag',
            'description' => 'Test description.',
            'usage_count' => 5,
        ]);

        $tag->decrementUsage();

        $this->assertEquals(4, $tag->fresh()->usage_count);
    }

    /** @test */
    public function it_cannot_decrement_usage_count_below_zero(): void
    {
        $tag = BlogTag::create([
            'name' => 'Zero Tag',
            'description' => 'Test description.',
            'usage_count' => 0,
        ]);

        $tag->decrementUsage();

        $this->assertEquals(0, $tag->fresh()->usage_count);
    }

    /** @test */
    public function it_can_reset_usage_count(): void
    {
        $tag = BlogTag::create([
            'name' => 'Reset Tag',
            'description' => 'Test description.',
            'usage_count' => 10,
        ]);

        $tag->resetUsage();

        $this->assertEquals(0, $tag->fresh()->usage_count);
    }

    /** @test */
    public function it_can_have_a_color(): void
    {
        $tag = BlogTag::create([
            'name' => 'Colored Tag',
            'description' => 'Test description.',
            'color' => '#ff5733',
        ]);

        $this->assertEquals('#ff5733', $tag->color);
    }

    /** @test */
    public function it_validates_hex_color_format(): void
    {
        $tag = BlogTag::create([
            'name' => 'Color Tag',
            'description' => 'Test description.',
            'color' => '#abc123',
        ]);

        $this->assertTrue($tag->hasValidColor());

        $tag->color = 'invalid-color';
        $this->assertFalse($tag->hasValidColor());

        $tag->color = '#xyz123';
        $this->assertFalse($tag->hasValidColor());
    }

    /** @test */
    public function it_can_generate_random_color(): void
    {
        $tag = BlogTag::create([
            'name' => 'Random Color Tag',
            'description' => 'Test description.',
        ]);

        $tag->generateRandomColor();

        $this->assertNotNull($tag->color);
        $this->assertTrue($tag->hasValidColor());
        $this->assertStringStartsWith('#', $tag->color);
        $this->assertEquals(7, strlen($tag->color));
    }

    /** @test */
    public function it_can_calculate_tag_cloud_weight(): void
    {
        // Create tags with different usage counts
        $tag1 = BlogTag::create(['name' => 'Tag 1', 'usage_count' => 1]);
        $tag2 = BlogTag::create(['name' => 'Tag 2', 'usage_count' => 5]);
        $tag3 = BlogTag::create(['name' => 'Tag 3', 'usage_count' => 10]);

        // Weight should be between 1 and 5 (default levels)
        $weight1 = $tag1->getTagCloudWeight();
        $weight2 = $tag2->getTagCloudWeight();
        $weight3 = $tag3->getTagCloudWeight();

        $this->assertGreaterThanOrEqual(1, $weight1);
        $this->assertLessThanOrEqual(5, $weight1);

        $this->assertGreaterThanOrEqual(1, $weight2);
        $this->assertLessThanOrEqual(5, $weight2);

        $this->assertGreaterThanOrEqual(1, $weight3);
        $this->assertLessThanOrEqual(5, $weight3);

        // Higher usage should have higher or equal weight
        $this->assertGreaterThanOrEqual($weight1, $weight2);
        $this->assertGreaterThanOrEqual($weight2, $weight3);
    }

    /** @test */
    public function it_can_scope_active_tags(): void
    {
        BlogTag::create(['name' => 'Active 1', 'is_active' => true]);
        BlogTag::create(['name' => 'Active 2', 'is_active' => true]);
        BlogTag::create(['name' => 'Inactive', 'is_active' => false]);

        $activeTags = BlogTag::active()->get();

        $this->assertCount(2, $activeTags);
    }

    /** @test */
    public function it_can_scope_popular_tags(): void
    {
        BlogTag::create(['name' => 'Popular 1', 'usage_count' => 10]);
        BlogTag::create(['name' => 'Popular 2', 'usage_count' => 8]);
        BlogTag::create(['name' => 'Unpopular', 'usage_count' => 1]);

        $popularTags = BlogTag::popular()->get();

        $this->assertCount(3, $popularTags);
        $this->assertEquals('Popular 1', $popularTags->first()->name);
        $this->assertEquals('Popular 2', $popularTags->get(1)->name);
    }

    /** @test */
    public function it_can_scope_tags_by_minimum_usage(): void
    {
        BlogTag::create(['name' => 'High Usage', 'usage_count' => 10]);
        BlogTag::create(['name' => 'Medium Usage', 'usage_count' => 5]);
        BlogTag::create(['name' => 'Low Usage', 'usage_count' => 1]);

        $tagsWithMinUsage = BlogTag::withMinimumUsage(5)->get();

        $this->assertCount(2, $tagsWithMinUsage);
        $this->assertTrue($tagsWithMinUsage->contains('name', 'High Usage'));
        $this->assertTrue($tagsWithMinUsage->contains('name', 'Medium Usage'));
    }

    /** @test */
    public function it_can_scope_unused_tags(): void
    {
        BlogTag::create(['name' => 'Used Tag', 'usage_count' => 5]);
        BlogTag::create(['name' => 'Unused Tag 1', 'usage_count' => 0]);
        BlogTag::create(['name' => 'Unused Tag 2', 'usage_count' => 0]);

        $unusedTags = BlogTag::unused()->get();

        $this->assertCount(2, $unusedTags);
        $this->assertTrue($unusedTags->contains('name', 'Unused Tag 1'));
        $this->assertTrue($unusedTags->contains('name', 'Unused Tag 2'));
    }

    /** @test */
    public function it_can_scope_tags_for_cloud(): void
    {
        BlogTag::create(['name' => 'Popular', 'usage_count' => 10, 'is_active' => true]);
        BlogTag::create(['name' => 'Medium', 'usage_count' => 5, 'is_active' => true]);
        BlogTag::create(['name' => 'Inactive', 'usage_count' => 8, 'is_active' => false]);
        BlogTag::create(['name' => 'Unused', 'usage_count' => 0, 'is_active' => true]);

        $cloudTags = BlogTag::forCloud()->get();

        $this->assertCount(2, $cloudTags);
        $this->assertEquals('Popular', $cloudTags->first()->name);
        $this->assertEquals('Medium', $cloudTags->get(1)->name);
    }

    /** @test */
    public function it_has_fillable_attributes(): void
    {
        $fillable = [
            'name', 'slug', 'description', 'color', 'usage_count', 'is_active',
        ];

        $tag = new BlogTag;

        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, $tag->getFillable());
        }
    }

    /** @test */
    public function it_can_find_or_create_tag_by_name(): void
    {
        // First call should create the tag
        $tag1 = BlogTag::findOrCreateByName('New Framework');

        $this->assertEquals('New Framework', $tag1->name);
        $this->assertEquals('new-framework', $tag1->slug);
        $this->assertEquals(1, BlogTag::count());

        // Second call should find existing tag
        $tag2 = BlogTag::findOrCreateByName('New Framework');

        $this->assertEquals($tag1->id, $tag2->id);
        $this->assertEquals(1, BlogTag::count());
    }

    /** @test */
    public function it_can_get_tag_cloud_data(): void
    {
        BlogTag::create(['name' => 'PHP', 'usage_count' => 10, 'is_active' => true]);
        BlogTag::create(['name' => 'JavaScript', 'usage_count' => 8, 'is_active' => true]);
        BlogTag::create(['name' => 'Laravel', 'usage_count' => 6, 'is_active' => true]);

        $cloudData = BlogTag::getTagCloudData();

        $this->assertCount(3, $cloudData);

        foreach ($cloudData as $tagData) {
            $this->assertArrayHasKey('name', $tagData);
            $this->assertArrayHasKey('slug', $tagData);
            $this->assertArrayHasKey('usage_count', $tagData);
            $this->assertArrayHasKey('weight', $tagData);
            $this->assertArrayHasKey('color', $tagData);
        }

        // Should be ordered by usage count descending
        $this->assertEquals('PHP', $cloudData[0]['name']);
        $this->assertEquals('JavaScript', $cloudData[1]['name']);
        $this->assertEquals('Laravel', $cloudData[2]['name']);
    }
}
