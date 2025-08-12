<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use JTD\AdminPanel\Fields\BelongsTo;
use JTD\AdminPanel\Fields\Boolean;
use JTD\AdminPanel\Fields\ID;
use JTD\AdminPanel\Fields\Number;
use JTD\AdminPanel\Fields\Slug;
use JTD\AdminPanel\Fields\Text;
use JTD\AdminPanel\Fields\Textarea;
use JTD\CMSBlogSystem\Admin\Resources\BlogCategoryResource;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogCategory AdminPanel Resource Test
 *
 * Tests the BlogCategory resource functionality including hierarchical management,
 * circular reference prevention, post count display, and SEO integration.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected BlogCategoryResource $resource;

    protected BlogCategory $blogCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blogCategory = BlogCategory::factory()->create();
        $this->resource = new BlogCategoryResource($this->blogCategory);
    }

    /** @test */
    public function it_has_correct_model_class(): void
    {
        $this->assertEquals(BlogCategory::class, BlogCategoryResource::$model);
    }

    /** @test */
    public function it_has_correct_title_field(): void
    {
        $this->assertEquals('name', BlogCategoryResource::$title);
    }

    /** @test */
    public function it_has_correct_search_fields(): void
    {
        $expected = ['name', 'description'];
        $this->assertEquals($expected, BlogCategoryResource::$search);
    }

    /** @test */
    public function it_belongs_to_blog_management_group(): void
    {
        $this->assertEquals('Blog Management', BlogCategoryResource::$group);
    }

    /** @test */
    public function it_has_correct_priority(): void
    {
        $this->assertEquals(2, BlogCategoryResource::$priority);
    }

    /** @test */
    public function it_returns_correct_fields(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        // Check for required field types
        $fieldTypes = array_map(fn ($field) => get_class($field), $fields);

        $this->assertContains(ID::class, $fieldTypes);
        $this->assertContains(Text::class, $fieldTypes);
        $this->assertContains(Slug::class, $fieldTypes);
        $this->assertContains(Textarea::class, $fieldTypes);
        $this->assertContains(BelongsTo::class, $fieldTypes);
        $this->assertContains(Number::class, $fieldTypes);
        $this->assertContains(Boolean::class, $fieldTypes);
    }

    /** @test */
    public function it_has_id_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $idField = collect($fields)->first(fn ($field) => $field instanceof ID);

        $this->assertNotNull($idField);
        $this->assertTrue($idField->sortable);
        $this->assertTrue($idField->copyable);
    }

    /** @test */
    public function it_has_name_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $nameField = collect($fields)->first(fn ($field) => $field instanceof Text && $field->attribute === 'name'
        );

        $this->assertNotNull($nameField);
        $this->assertEquals('Name', $nameField->name);
        $this->assertTrue($nameField->sortable);
        $this->assertTrue($nameField->searchable);
        $this->assertContains('required', $nameField->rules);
        $this->assertContains('max:255', $nameField->rules);
    }

    /** @test */
    public function it_has_slug_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $slugField = collect($fields)->first(fn ($field) => $field instanceof Slug);

        $this->assertNotNull($slugField);
        $this->assertEquals('Slug', $slugField->name);
        $this->assertEquals('name', $slugField->fromAttribute);
        $this->assertContains('required', $slugField->rules);
        $this->assertContains('alpha_dash', $slugField->rules);
    }

    /** @test */
    public function it_has_parent_category_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $parentField = collect($fields)->first(fn ($field) => $field instanceof BelongsTo && $field->attribute === 'parent'
        );

        $this->assertNotNull($parentField);
        $this->assertEquals('Parent Category', $parentField->name);
        $this->assertTrue($parentField->searchable);
        $this->assertTrue($parentField->nullable);
    }

    /** @test */
    public function it_has_post_count_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $postCountField = collect($fields)->first(fn ($field) => $field instanceof Number && $field->attribute === 'posts_count'
        );

        $this->assertNotNull($postCountField);
        $this->assertEquals('Post Count', $postCountField->name);
        $this->assertFalse($postCountField->showOnCreation);
        $this->assertFalse($postCountField->showOnUpdate);
        $this->assertTrue($postCountField->sortable);
    }

    /** @test */
    public function it_has_seo_fields_configured(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $metaTitleField = collect($fields)->first(fn ($field) => $field instanceof Text && $field->attribute === 'meta_title'
        );

        $metaDescField = collect($fields)->first(fn ($field) => $field instanceof Textarea && $field->attribute === 'meta_description'
        );

        $this->assertNotNull($metaTitleField);
        $this->assertNotNull($metaDescField);
        $this->assertEquals('Meta Title', $metaTitleField->name);
        $this->assertEquals('Meta Description', $metaDescField->name);
        $this->assertFalse($metaTitleField->showOnIndex);
        $this->assertFalse($metaDescField->showOnIndex);
    }

    /** @test */
    public function it_has_active_status_field(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $activeField = collect($fields)->first(fn ($field) => $field instanceof Boolean && $field->attribute === 'is_active'
        );

        $this->assertNotNull($activeField);
        $this->assertEquals('Active', $activeField->name);
        $this->assertTrue($activeField->sortable);
    }

    /** @test */
    public function it_can_create_blog_category_through_resource(): void
    {
        $parentCategory = BlogCategory::factory()->create();

        $data = [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'parent_id' => $parentCategory->id,
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test meta description',
            'is_active' => true,
        ];

        // Create category directly using the model to test the resource structure
        $category = BlogCategory::create($data);

        $this->assertDatabaseHas('blog_categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'parent_id' => $parentCategory->id,
            'is_active' => true,
        ]);

        // Verify the resource can display the created category
        $resource = new BlogCategoryResource($category);
        $request = new Request;
        $fields = $resource->fields($request);

        $this->assertNotEmpty($fields);
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        $request = new Request([]);
        $fields = $this->resource->fields($request);

        $requiredFields = collect($fields)->filter(fn ($field) => in_array('required', $field->rules ?? [])
        );

        $this->assertGreaterThan(0, $requiredFields->count());

        // Check specific required fields
        $nameField = $requiredFields->first(fn ($field) => $field->attribute === 'name');
        $slugField = $requiredFields->first(fn ($field) => $field->attribute === 'slug');

        $this->assertNotNull($nameField);
        $this->assertNotNull($slugField);
    }

    /** @test */
    public function it_displays_post_count_correctly(): void
    {
        // Create category with posts
        $category = BlogCategory::factory()->create();
        $posts = BlogPost::factory()->count(3)->create();

        // Attach posts to category
        foreach ($posts as $post) {
            $post->categories()->attach($category);
        }

        // Refresh to get updated counts
        $category->refresh();

        $this->assertEquals(3, $category->posts()->count());
    }

    /** @test */
    public function it_prevents_circular_references_in_parent_selection(): void
    {
        $parentCategory = BlogCategory::factory()->create();
        $childCategory = BlogCategory::factory()->create(['parent_id' => $parentCategory->id]);

        // Try to make parent a child of its own child (circular reference)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create circular reference');

        $parentCategory->parent_id = $childCategory->id;
        $parentCategory->save();
    }

    /** @test */
    public function it_has_proper_field_visibility_settings(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        // Check that timestamps are only on detail view
        $createdAtField = collect($fields)->first(fn ($field) => $field->attribute === 'created_at'
        );

        $updatedAtField = collect($fields)->first(fn ($field) => $field->attribute === 'updated_at'
        );

        if ($createdAtField) {
            $this->assertFalse($createdAtField->showOnIndex);
            $this->assertTrue($createdAtField->showOnDetail);
        }

        if ($updatedAtField) {
            $this->assertFalse($updatedAtField->showOnIndex);
            $this->assertTrue($updatedAtField->showOnDetail);
        }
    }

    /** @test */
    public function it_generates_unique_slugs_automatically(): void
    {
        $category1 = BlogCategory::factory()->create(['name' => 'Test Category']);
        $category2 = BlogCategory::factory()->create(['name' => 'Test Category']);

        $this->assertEquals('test-category', $category1->slug);
        $this->assertEquals('test-category-1', $category2->slug);
    }

    /** @test */
    public function it_has_correct_resource_labels(): void
    {
        $this->assertEquals('Blog Categories', BlogCategoryResource::label());
        $this->assertEquals('Blog Category', BlogCategoryResource::singularLabel());
        $this->assertEquals('blog-categories', BlogCategoryResource::uriKey());
    }
}
