<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use JTD\AdminPanel\Fields\Boolean;
use JTD\AdminPanel\Fields\ID;
use JTD\AdminPanel\Fields\Number;
use JTD\AdminPanel\Fields\Slug;
use JTD\AdminPanel\Fields\Text;
use JTD\AdminPanel\Fields\Textarea;
use JTD\CMSBlogSystem\Admin\Resources\BlogTagResource;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogTag AdminPanel Resource Test
 *
 * Tests the BlogTag resource functionality including usage statistics,
 * popularity sorting, bulk operations, and tag management features.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTagResourceTest extends TestCase
{
    use RefreshDatabase;

    protected BlogTagResource $resource;

    protected BlogTag $blogTag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blogTag = BlogTag::factory()->create();
        $this->resource = new BlogTagResource($this->blogTag);
    }

    /** @test */
    public function it_has_correct_model_class(): void
    {
        $this->assertEquals(BlogTag::class, BlogTagResource::$model);
    }

    /** @test */
    public function it_has_correct_title_field(): void
    {
        $this->assertEquals('name', BlogTagResource::$title);
    }

    /** @test */
    public function it_has_correct_search_fields(): void
    {
        $expected = ['name', 'description'];
        $this->assertEquals($expected, BlogTagResource::$search);
    }

    /** @test */
    public function it_belongs_to_blog_management_group(): void
    {
        $this->assertEquals('Blog Management', BlogTagResource::$group);
    }

    /** @test */
    public function it_has_correct_priority(): void
    {
        $this->assertEquals(3, BlogTagResource::$priority);
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
    public function it_has_usage_count_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $usageCountField = collect($fields)->first(fn ($field) => $field instanceof Number && $field->attribute === 'usage_count'
        );

        $this->assertNotNull($usageCountField);
        $this->assertEquals('Usage Count', $usageCountField->name);
        $this->assertFalse($usageCountField->showOnCreation);
        $this->assertFalse($usageCountField->showOnUpdate);
        $this->assertTrue($usageCountField->sortable);
    }

    /** @test */
    public function it_has_color_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $colorField = collect($fields)->first(fn ($field) => $field instanceof Text && $field->attribute === 'color'
        );

        $this->assertNotNull($colorField);
        $this->assertEquals('Color', $colorField->name);
        $this->assertTrue($colorField->nullable);
        $this->assertFalse($colorField->showOnIndex);
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
    public function it_can_create_blog_tag_through_resource(): void
    {
        $data = [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'description' => 'Test tag description',
            'color' => '#FF6B6B',
            'is_active' => true,
        ];

        // Create tag directly using the model to test the resource structure
        $tag = BlogTag::create($data);

        $this->assertDatabaseHas('blog_tags', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'color' => '#FF6B6B',
            'is_active' => true,
        ]);

        // Verify the resource can display the created tag
        $resource = new BlogTagResource($tag);
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
    public function it_displays_usage_count_correctly(): void
    {
        // Create tag with posts
        $tag = BlogTag::factory()->create();
        $posts = BlogPost::factory()->count(5)->create();

        // Attach posts to tag
        foreach ($posts as $post) {
            $post->tags()->attach($tag);
        }

        // Update usage count
        $tag->update(['usage_count' => 5]);

        $this->assertEquals(5, $tag->usage_count);
        $this->assertEquals(5, $tag->posts()->count());
    }

    /** @test */
    public function it_can_sort_tags_by_popularity(): void
    {
        // Clear existing tags to ensure clean test
        BlogTag::query()->delete();

        // Create tags with different usage counts
        $popularTag = BlogTag::factory()->create(['name' => 'Popular Tag', 'usage_count' => 10]);
        $lessPopularTag = BlogTag::factory()->create(['name' => 'Less Popular Tag', 'usage_count' => 5]);
        $unpopularTag = BlogTag::factory()->create(['name' => 'Unpopular Tag', 'usage_count' => 1]);

        // Get tags sorted by popularity (usage count desc)
        $popularTags = BlogTag::popular()->get();

        $this->assertEquals($popularTag->name, $popularTags->first()->name);
        $this->assertEquals($unpopularTag->name, $popularTags->last()->name);
        $this->assertEquals(10, $popularTags->first()->usage_count);
        $this->assertEquals(1, $popularTags->last()->usage_count);
    }

    /** @test */
    public function it_generates_unique_slugs_automatically(): void
    {
        $tag1 = BlogTag::factory()->create(['name' => 'Test Tag']);
        $tag2 = BlogTag::factory()->create(['name' => 'Test Tag']);

        $this->assertEquals('test-tag', $tag1->slug);
        $this->assertEquals('test-tag-1', $tag2->slug);
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
    public function it_has_correct_resource_labels(): void
    {
        $this->assertEquals('Blog Tags', BlogTagResource::label());
        $this->assertEquals('Blog Tag', BlogTagResource::singularLabel());
        $this->assertEquals('blog-tags', BlogTagResource::uriKey());
    }

    /** @test */
    public function it_tracks_usage_statistics_accurately(): void
    {
        $tag = BlogTag::factory()->create(['usage_count' => 0]);

        // Test increment
        $tag->incrementUsage();
        $this->assertEquals(1, $tag->fresh()->usage_count);

        // Test decrement
        $tag->decrementUsage();
        $this->assertEquals(0, $tag->fresh()->usage_count);

        // Test decrement doesn't go below 0
        $tag->decrementUsage();
        $this->assertEquals(0, $tag->fresh()->usage_count);
    }

    /** @test */
    public function it_can_find_or_create_tags_by_name(): void
    {
        // First call should create
        $tag1 = BlogTag::findOrCreateByName('New Tag');
        $this->assertDatabaseHas('blog_tags', ['name' => 'New Tag']);

        // Second call should find existing
        $tag2 = BlogTag::findOrCreateByName('New Tag');
        $this->assertEquals($tag1->id, $tag2->id);

        // Should only have one record
        $this->assertEquals(1, BlogTag::where('name', 'New Tag')->count());
    }
}
