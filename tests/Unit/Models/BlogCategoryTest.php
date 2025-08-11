<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogCategory Model Test
 *
 * Tests the BlogCategory model functionality including hierarchical relationships,
 * slug generation, SEO fields, and post count tracking.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_blog_category(): void
    {
        $category = BlogCategory::create([
            'name' => 'Technology',
            'description' => 'Articles about technology and programming.',
        ]);

        $this->assertInstanceOf(BlogCategory::class, $category);
        $this->assertEquals('Technology', $category->name);
        $this->assertEquals('Articles about technology and programming.', $category->description);
        $this->assertNotNull($category->slug);
        $this->assertTrue($category->is_active);
    }

    /** @test */
    public function it_automatically_generates_slug_from_name(): void
    {
        $category = BlogCategory::create([
            'name' => 'Web Development & Design',
            'description' => 'Web development articles.',
        ]);

        $this->assertEquals('web-development-design', $category->slug);
    }

    /** @test */
    public function it_ensures_slug_uniqueness(): void
    {
        // Create first category
        BlogCategory::create([
            'name' => 'Programming',
            'description' => 'First programming category.',
        ]);

        // Create second category with same name
        $secondCategory = BlogCategory::create([
            'name' => 'Programming',
            'description' => 'Second programming category.',
        ]);

        $this->assertEquals('programming', BlogCategory::first()->slug);
        $this->assertStringStartsWith('programming-', $secondCategory->slug);
        $this->assertNotEquals(BlogCategory::first()->slug, $secondCategory->slug);
    }

    /** @test */
    public function it_can_set_custom_slug(): void
    {
        $category = BlogCategory::create([
            'name' => 'JavaScript',
            'slug' => 'js-programming',
            'description' => 'JavaScript articles.',
        ]);

        $this->assertEquals('js-programming', $category->slug);
    }

    /** @test */
    public function it_defaults_to_active_status(): void
    {
        $category = BlogCategory::create([
            'name' => 'Test Category',
            'description' => 'Test description.',
        ]);

        $this->assertTrue($category->is_active);
    }

    /** @test */
    public function it_can_be_inactive(): void
    {
        $category = BlogCategory::create([
            'name' => 'Inactive Category',
            'description' => 'Test description.',
            'is_active' => false,
        ]);

        $this->assertFalse($category->is_active);
    }

    /** @test */
    public function it_has_seo_fields(): void
    {
        $category = BlogCategory::create([
            'name' => 'SEO Category',
            'description' => 'Test description.',
            'meta_title' => 'Custom SEO Title',
            'meta_description' => 'This is a custom meta description for SEO.',
        ]);

        $this->assertEquals('Custom SEO Title', $category->meta_title);
        $this->assertEquals('This is a custom meta description for SEO.', $category->meta_description);
    }

    /** @test */
    public function it_can_have_a_parent_category(): void
    {
        $parent = BlogCategory::create([
            'name' => 'Programming',
            'description' => 'Programming articles.',
        ]);

        $child = BlogCategory::create([
            'name' => 'PHP',
            'description' => 'PHP programming articles.',
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertInstanceOf(BlogCategory::class, $child->parent);
        $this->assertEquals('Programming', $child->parent->name);
    }

    /** @test */
    public function it_can_have_multiple_children(): void
    {
        $parent = BlogCategory::create([
            'name' => 'Web Development',
            'description' => 'Web development articles.',
        ]);

        $child1 = BlogCategory::create([
            'name' => 'Frontend',
            'description' => 'Frontend development.',
            'parent_id' => $parent->id,
        ]);

        $child2 = BlogCategory::create([
            'name' => 'Backend',
            'description' => 'Backend development.',
            'parent_id' => $parent->id,
        ]);

        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->contains($child1));
        $this->assertTrue($parent->children->contains($child2));
    }

    /** @test */
    public function it_can_get_all_descendants(): void
    {
        $grandparent = BlogCategory::create(['name' => 'Technology', 'description' => 'Tech articles.']);
        $parent = BlogCategory::create(['name' => 'Programming', 'description' => 'Programming articles.', 'parent_id' => $grandparent->id]);
        $child1 = BlogCategory::create(['name' => 'PHP', 'description' => 'PHP articles.', 'parent_id' => $parent->id]);
        $child2 = BlogCategory::create(['name' => 'JavaScript', 'description' => 'JS articles.', 'parent_id' => $parent->id]);

        $descendants = $grandparent->getAllDescendants();

        $this->assertCount(3, $descendants);
        $this->assertTrue($descendants->contains($parent));
        $this->assertTrue($descendants->contains($child1));
        $this->assertTrue($descendants->contains($child2));
    }

    /** @test */
    public function it_can_get_all_ancestors(): void
    {
        $grandparent = BlogCategory::create(['name' => 'Technology', 'description' => 'Tech articles.']);
        $parent = BlogCategory::create(['name' => 'Programming', 'description' => 'Programming articles.', 'parent_id' => $grandparent->id]);
        $child = BlogCategory::create(['name' => 'PHP', 'description' => 'PHP articles.', 'parent_id' => $parent->id]);

        $ancestors = $child->getAllAncestors();

        $this->assertCount(2, $ancestors);
        $this->assertTrue($ancestors->contains($parent));
        $this->assertTrue($ancestors->contains($grandparent));
    }

    /** @test */
    public function it_can_check_if_root_category(): void
    {
        $root = BlogCategory::create(['name' => 'Root Category', 'description' => 'Root description.']);
        $child = BlogCategory::create(['name' => 'Child Category', 'description' => 'Child description.', 'parent_id' => $root->id]);

        $this->assertTrue($root->isRoot());
        $this->assertFalse($child->isRoot());
    }

    /** @test */
    public function it_can_check_if_leaf_category(): void
    {
        $parent = BlogCategory::create(['name' => 'Parent Category', 'description' => 'Parent description.']);
        $child = BlogCategory::create(['name' => 'Child Category', 'description' => 'Child description.', 'parent_id' => $parent->id]);

        $this->assertFalse($parent->isLeaf());
        $this->assertTrue($child->isLeaf());
    }

    /** @test */
    public function it_can_get_depth_level(): void
    {
        $level0 = BlogCategory::create(['name' => 'Level 0', 'description' => 'Root level.']);
        $level1 = BlogCategory::create(['name' => 'Level 1', 'description' => 'First level.', 'parent_id' => $level0->id]);
        $level2 = BlogCategory::create(['name' => 'Level 2', 'description' => 'Second level.', 'parent_id' => $level1->id]);

        $this->assertEquals(0, $level0->getDepth());
        $this->assertEquals(1, $level1->getDepth());
        $this->assertEquals(2, $level2->getDepth());
    }

    /** @test */
    public function it_can_scope_root_categories(): void
    {
        $root1 = BlogCategory::create(['name' => 'Root 1', 'description' => 'First root.']);
        $root2 = BlogCategory::create(['name' => 'Root 2', 'description' => 'Second root.']);
        BlogCategory::create(['name' => 'Child', 'description' => 'Child category.', 'parent_id' => $root1->id]);

        $rootCategories = BlogCategory::roots()->get();

        $this->assertCount(2, $rootCategories);
        $this->assertTrue($rootCategories->contains($root1));
        $this->assertTrue($rootCategories->contains($root2));
    }

    /** @test */
    public function it_can_scope_active_categories(): void
    {
        BlogCategory::create(['name' => 'Active 1', 'description' => 'Active category.', 'is_active' => true]);
        BlogCategory::create(['name' => 'Active 2', 'description' => 'Active category.', 'is_active' => true]);
        BlogCategory::create(['name' => 'Inactive', 'description' => 'Inactive category.', 'is_active' => false]);

        $activeCategories = BlogCategory::active()->get();

        $this->assertCount(2, $activeCategories);
    }

    /** @test */
    public function it_can_scope_ordered_categories(): void
    {
        BlogCategory::create(['name' => 'Third', 'description' => 'Third category.', 'sort_order' => 30]);
        BlogCategory::create(['name' => 'First', 'description' => 'First category.', 'sort_order' => 10]);
        BlogCategory::create(['name' => 'Second', 'description' => 'Second category.', 'sort_order' => 20]);

        $orderedCategories = BlogCategory::ordered()->get();

        $this->assertEquals('First', $orderedCategories->first()->name);
        $this->assertEquals('Third', $orderedCategories->last()->name);
    }

    /** @test */
    public function it_has_fillable_attributes(): void
    {
        $fillable = [
            'name', 'slug', 'description', 'parent_id', 'sort_order', 'is_active',
            'meta_title', 'meta_description',
        ];

        $category = new BlogCategory;

        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, $category->getFillable());
        }
    }

    /** @test */
    public function it_prevents_circular_references(): void
    {
        $parent = BlogCategory::create(['name' => 'Parent', 'description' => 'Parent category.']);
        $child = BlogCategory::create(['name' => 'Child', 'description' => 'Child category.', 'parent_id' => $parent->id]);

        // Try to make parent a child of its own child (should fail)
        $this->expectException(\InvalidArgumentException::class);
        $parent->update(['parent_id' => $child->id]);
    }
}
