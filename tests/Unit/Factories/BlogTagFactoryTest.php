<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogTag Factory Test
 *
 * Tests the BlogTag factory functionality and states.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTagFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_blog_tag_using_factory(): void
    {
        $tag = BlogTag::factory()->create();

        $this->assertInstanceOf(BlogTag::class, $tag);
        $this->assertNotEmpty($tag->name);
        $this->assertNotEmpty($tag->slug);
        $this->assertTrue($tag->is_active);
        $this->assertIsInt($tag->usage_count);
        $this->assertGreaterThanOrEqual(0, $tag->usage_count);
    }

    /** @test */
    public function it_can_create_active_tags(): void
    {
        $tag = BlogTag::factory()->active()->create();

        $this->assertTrue($tag->is_active);
    }

    /** @test */
    public function it_can_create_inactive_tags(): void
    {
        $tag = BlogTag::factory()->inactive()->create();

        $this->assertFalse($tag->is_active);
    }

    /** @test */
    public function it_can_create_popular_tags(): void
    {
        $tag = BlogTag::factory()->popular()->create();

        $this->assertGreaterThanOrEqual(20, $tag->usage_count);
        $this->assertLessThanOrEqual(100, $tag->usage_count);
    }

    /** @test */
    public function it_can_create_unused_tags(): void
    {
        $tag = BlogTag::factory()->unused()->create();

        $this->assertEquals(0, $tag->usage_count);
    }

    /** @test */
    public function it_can_create_tags_with_specific_usage_count(): void
    {
        $tag = BlogTag::factory()->withUsageCount(42)->create();

        $this->assertEquals(42, $tag->usage_count);
    }

    /** @test */
    public function it_can_create_tags_with_specific_color(): void
    {
        $tag = BlogTag::factory()->withColor('#FF5733')->create();

        $this->assertEquals('#FF5733', $tag->color);
    }

    /** @test */
    public function it_can_create_tags_with_specific_name(): void
    {
        $tag = BlogTag::factory()->withName('Custom Tag')->create();

        $this->assertEquals('Custom Tag', $tag->name);
        $this->assertEquals('custom-tag', $tag->slug);
    }

    /** @test */
    public function it_can_create_technology_tags(): void
    {
        $tag = BlogTag::factory()->technology()->create();

        $this->assertNotEmpty($tag->name);
        $this->assertNotEmpty($tag->description);
        $this->assertNotEmpty($tag->color);
        $this->assertStringContainsString('development', strtolower($tag->description));
    }

    /** @test */
    public function it_can_create_business_tags(): void
    {
        $tag = BlogTag::factory()->business()->create();

        $this->assertNotEmpty($tag->name);
        $this->assertNotEmpty($tag->description);
        $this->assertNotEmpty($tag->color);
        $this->assertStringContainsString('business', strtolower($tag->description));
    }

    /** @test */
    public function it_can_create_lifestyle_tags(): void
    {
        $tag = BlogTag::factory()->lifestyle()->create();

        $this->assertNotEmpty($tag->name);
        $this->assertNotEmpty($tag->description);
        $this->assertNotEmpty($tag->color);
        $this->assertStringContainsString('lifestyle', strtolower($tag->description));
    }

    /** @test */
    public function it_can_create_trending_tags(): void
    {
        $tag = BlogTag::factory()->trending()->create();

        $this->assertGreaterThanOrEqual(15, $tag->usage_count);
        $this->assertLessThanOrEqual(40, $tag->usage_count);
        $this->assertTrue($tag->updated_at->isAfter(now()->subWeek()));
    }

    /** @test */
    public function it_can_create_tags_with_description(): void
    {
        $tag = BlogTag::factory()->withDescription()->create();

        $this->assertNotEmpty($tag->description);
        $this->assertGreaterThan(10, strlen($tag->description));
    }

    /** @test */
    public function it_can_create_multiple_tags_with_unique_slugs(): void
    {
        $tags = BlogTag::factory()->count(5)->create();

        $this->assertCount(5, $tags);

        // Ensure all tags have unique slugs
        $slugs = $tags->pluck('slug')->toArray();
        $this->assertCount(5, array_unique($slugs));
    }

    /** @test */
    public function it_can_combine_states(): void
    {
        $tag = BlogTag::factory()
            ->popular()
            ->active()
            ->withColor('#FF5733')
            ->withName('Combined Tag')
            ->create();

        $this->assertEquals('Combined Tag', $tag->name);
        $this->assertTrue($tag->is_active);
        $this->assertEquals('#FF5733', $tag->color);
        $this->assertGreaterThanOrEqual(20, $tag->usage_count);
    }

    /** @test */
    public function it_creates_valid_hex_colors_for_themed_tags(): void
    {
        $techTag = BlogTag::factory()->technology()->create();
        $businessTag = BlogTag::factory()->business()->create();
        $lifestyleTag = BlogTag::factory()->lifestyle()->create();

        $this->assertTrue($techTag->hasValidColor());
        $this->assertTrue($businessTag->hasValidColor());
        $this->assertTrue($lifestyleTag->hasValidColor());
    }

    /** @test */
    public function it_can_create_tag_cloud_dataset(): void
    {
        // Create a variety of tags for tag cloud
        BlogTag::factory()->withName('PHP')->withUsageCount(50)->withColor('#777BB4')->create();
        BlogTag::factory()->withName('JavaScript')->withUsageCount(45)->withColor('#F7DF1E')->create();
        BlogTag::factory()->withName('Laravel')->withUsageCount(40)->withColor('#FF2D20')->create();
        BlogTag::factory()->withName('Vue.js')->withUsageCount(35)->withColor('#4FC08D')->create();
        BlogTag::factory()->withName('React')->withUsageCount(30)->withColor('#61DAFB')->create();

        $cloudData = BlogTag::getTagCloudData();

        $this->assertCount(5, $cloudData);

        // Should be ordered by usage count descending
        $this->assertEquals('PHP', $cloudData[0]['name']);
        $this->assertEquals('JavaScript', $cloudData[1]['name']);
        $this->assertEquals('Laravel', $cloudData[2]['name']);

        // Check data structure
        foreach ($cloudData as $tagData) {
            $this->assertArrayHasKey('name', $tagData);
            $this->assertArrayHasKey('slug', $tagData);
            $this->assertArrayHasKey('usage_count', $tagData);
            $this->assertArrayHasKey('weight', $tagData);
            $this->assertArrayHasKey('color', $tagData);
        }
    }

    /** @test */
    public function it_can_create_realistic_tag_ecosystem(): void
    {
        // Create a realistic mix of tags
        $popularTags = BlogTag::factory()->count(5)->popular()->technology()->create();
        $mediumTags = BlogTag::factory()->count(8)->withUsageCount(10)->business()->create();
        $newTags = BlogTag::factory()->count(12)->unused()->lifestyle()->create();
        $inactiveTags = BlogTag::factory()->count(3)->inactive()->create();

        // Test popular tags
        $popular = BlogTag::getPopularTags(10);
        $this->assertGreaterThan(0, $popular->count());
        $this->assertTrue($popular->every(fn ($tag) => $tag->is_active));

        // Test trending tags
        $trending = BlogTag::getTrendingTags(30, 5);
        $this->assertGreaterThanOrEqual(0, $trending->count());

        // Test tag cloud
        $cloudData = BlogTag::getTagCloudData(20);
        $this->assertGreaterThan(0, count($cloudData));

        // Test cleanup
        $cleanedUp = BlogTag::cleanupUnused(0); // Clean up unused tags created today
        $this->assertGreaterThanOrEqual(0, $cleanedUp);
    }

    /** @test */
    public function it_maintains_proper_tag_weights_in_cloud(): void
    {
        // Create tags with specific usage counts for weight testing
        $highUsage = BlogTag::factory()->withName('High')->withUsageCount(100)->create();
        $mediumUsage = BlogTag::factory()->withName('Medium')->withUsageCount(50)->create();
        $lowUsage = BlogTag::factory()->withName('Low')->withUsageCount(10)->create();

        $highWeight = $highUsage->getTagCloudWeight();
        $mediumWeight = $mediumUsage->getTagCloudWeight();
        $lowWeight = $lowUsage->getTagCloudWeight();

        // Higher usage should have higher or equal weight
        $this->assertGreaterThanOrEqual($mediumWeight, $highWeight);
        $this->assertGreaterThanOrEqual($lowWeight, $mediumWeight);

        // All weights should be within valid range (1-5)
        $this->assertGreaterThanOrEqual(1, $lowWeight);
        $this->assertLessThanOrEqual(5, $highWeight);
    }
}
