<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogCategory Factory Test
 *
 * Tests the BlogCategory factory functionality and states.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogCategoryFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_blog_category_using_factory(): void
    {
        $category = BlogCategory::factory()->create();

        $this->assertInstanceOf(BlogCategory::class, $category);
        $this->assertNotEmpty($category->name);
        $this->assertNotEmpty($category->slug);
        $this->assertTrue($category->is_active);
        $this->assertIsInt($category->sort_order);
    }

    /** @test */
    public function it_can_create_root_categories(): void
    {
        $category = BlogCategory::factory()->root()->create();

        $this->assertNull($category->parent_id);
        $this->assertTrue($category->isRoot());
    }

    /** @test */
    public function it_can_create_child_categories(): void
    {
        $parent = BlogCategory::factory()->create();
        $child = BlogCategory::factory()->childOf($parent)->create();

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertFalse($child->isRoot());
        $this->assertEquals($parent->name, $child->parent->name);
    }

    /** @test */
    public function it_can_create_active_categories(): void
    {
        $category = BlogCategory::factory()->active()->create();

        $this->assertTrue($category->is_active);
    }

    /** @test */
    public function it_can_create_inactive_categories(): void
    {
        $category = BlogCategory::factory()->inactive()->create();

        $this->assertFalse($category->is_active);
    }

    /** @test */
    public function it_can_create_categories_with_seo_fields(): void
    {
        $category = BlogCategory::factory()->withSeo()->create();

        $this->assertNotNull($category->meta_title);
        $this->assertNotNull($category->meta_description);
    }

    /** @test */
    public function it_can_create_categories_with_specific_sort_order(): void
    {
        $category = BlogCategory::factory()->withSortOrder(50)->create();

        $this->assertEquals(50, $category->sort_order);
    }

    /** @test */
    public function it_can_create_categories_with_specific_name(): void
    {
        $category = BlogCategory::factory()->withName('Custom Category')->create();

        $this->assertEquals('Custom Category', $category->name);
        $this->assertEquals('custom-category', $category->slug);
    }

    /** @test */
    public function it_can_create_technology_categories(): void
    {
        $category = BlogCategory::factory()->technology()->create();

        $this->assertNotEmpty($category->name);
        $this->assertNotEmpty($category->description);
        $this->assertStringContainsString('development', strtolower($category->description));
    }

    /** @test */
    public function it_can_create_business_categories(): void
    {
        $category = BlogCategory::factory()->business()->create();

        $this->assertNotEmpty($category->name);
        $this->assertNotEmpty($category->description);
        $this->assertStringContainsString('business', strtolower($category->description));
    }

    /** @test */
    public function it_can_create_lifestyle_categories(): void
    {
        $category = BlogCategory::factory()->lifestyle()->create();

        $this->assertNotEmpty($category->name);
        $this->assertNotEmpty($category->description);
        $this->assertStringContainsString('lifestyle', strtolower($category->description));
    }

    /** @test */
    public function it_can_create_categories_with_children(): void
    {
        $parent = BlogCategory::factory()->withChildren(3)->create();

        $this->assertCount(3, $parent->children);

        foreach ($parent->children as $child) {
            $this->assertEquals($parent->id, $child->parent_id);
        }
    }

    /** @test */
    public function it_can_create_deep_hierarchy(): void
    {
        $grandparent = BlogCategory::factory()->deepHierarchy()->create();

        // Should have 2 direct children (parents)
        $this->assertCount(2, $grandparent->children);

        // Each parent should have 3 children
        foreach ($grandparent->children as $parent) {
            $this->assertCount(3, $parent->children);

            // Each grandchild should have the correct parent
            foreach ($parent->children as $child) {
                $this->assertEquals($parent->id, $child->parent_id);
            }
        }

        // Total descendants should be 8 (2 parents + 6 grandchildren)
        $this->assertCount(8, $grandparent->getAllDescendants());
    }

    /** @test */
    public function it_can_create_multiple_categories_with_unique_slugs(): void
    {
        $categories = BlogCategory::factory()->count(5)->create();

        $this->assertCount(5, $categories);

        // Ensure all categories have unique slugs
        $slugs = $categories->pluck('slug')->toArray();
        $this->assertCount(5, array_unique($slugs));
    }

    /** @test */
    public function it_can_combine_states(): void
    {
        $parent = BlogCategory::factory()->create();
        $category = BlogCategory::factory()
            ->childOf($parent)
            ->active()
            ->withSeo()
            ->withSortOrder(25)
            ->create();

        $this->assertEquals($parent->id, $category->parent_id);
        $this->assertTrue($category->is_active);
        $this->assertNotNull($category->meta_title);
        $this->assertEquals(25, $category->sort_order);
    }

    /** @test */
    public function it_creates_categories_with_proper_hierarchy_structure(): void
    {
        $root = BlogCategory::factory()->withName('Technology')->create();
        $child1 = BlogCategory::factory()->childOf($root)->withName('Frontend')->create();
        $child2 = BlogCategory::factory()->childOf($root)->withName('Backend')->create();
        $grandchild = BlogCategory::factory()->childOf($child1)->withName('React')->create();

        // Test hierarchy relationships
        $this->assertTrue($root->isRoot());
        $this->assertFalse($root->isLeaf());
        $this->assertEquals(0, $root->getDepth());

        $this->assertFalse($child1->isRoot());
        $this->assertFalse($child1->isLeaf());
        $this->assertEquals(1, $child1->getDepth());

        $this->assertFalse($child2->isRoot());
        $this->assertTrue($child2->isLeaf());
        $this->assertEquals(1, $child2->getDepth());

        $this->assertFalse($grandchild->isRoot());
        $this->assertTrue($grandchild->isLeaf());
        $this->assertEquals(2, $grandchild->getDepth());

        // Test path generation
        $this->assertEquals('Technology > Frontend > React', $grandchild->getFullPath());
        $this->assertEquals('technology/frontend/react', $grandchild->getUrlPath());
    }
}
