<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use JTD\AdminPanel\Fields\DateTime;
use JTD\AdminPanel\Fields\ID;
use JTD\AdminPanel\Fields\ManyToMany;
use JTD\AdminPanel\Fields\Markdown;
use JTD\AdminPanel\Fields\MediaLibraryImage;
use JTD\AdminPanel\Fields\Select;
use JTD\AdminPanel\Fields\Slug;
use JTD\AdminPanel\Fields\Text;
use JTD\AdminPanel\Fields\Textarea;
use JTD\CMSBlogSystem\Admin\Resources\BlogPostResource;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * BlogPost AdminPanel Resource Test
 *
 * Tests the BlogPost resource functionality including field configuration,
 * validation, relationships, and AdminPanel integration.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPostResourceTest extends TestCase
{
    use RefreshDatabase;

    protected BlogPostResource $resource;

    protected BlogPost $blogPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blogPost = BlogPost::factory()->create();
        $this->resource = new BlogPostResource($this->blogPost);
    }

    /** @test */
    public function it_has_correct_model_class(): void
    {
        $this->assertEquals(BlogPost::class, BlogPostResource::$model);
    }

    /** @test */
    public function it_has_correct_title_field(): void
    {
        $this->assertEquals('title', BlogPostResource::$title);
    }

    /** @test */
    public function it_has_correct_search_fields(): void
    {
        $expected = ['title', 'content', 'excerpt'];
        $this->assertEquals($expected, BlogPostResource::$search);
    }

    /** @test */
    public function it_belongs_to_blog_management_group(): void
    {
        $this->assertEquals('Blog Management', BlogPostResource::$group);
    }

    /** @test */
    public function it_has_correct_priority(): void
    {
        $this->assertEquals(1, BlogPostResource::$priority);
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
        $this->assertContains(Markdown::class, $fieldTypes);
        $this->assertContains(Select::class, $fieldTypes);
        $this->assertContains(DateTime::class, $fieldTypes);
        $this->assertContains(ManyToMany::class, $fieldTypes);
        $this->assertContains(MediaLibraryImage::class, $fieldTypes);
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
    public function it_has_title_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $titleField = collect($fields)->first(fn ($field) => $field instanceof Text && $field->attribute === 'title'
        );

        $this->assertNotNull($titleField);
        $this->assertEquals('Title', $titleField->name);
        $this->assertTrue($titleField->sortable);
        $this->assertTrue($titleField->searchable);
        $this->assertContains('required', $titleField->rules);
        $this->assertContains('max:255', $titleField->rules);
    }

    /** @test */
    public function it_has_slug_field_with_correct_configuration(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $slugField = collect($fields)->first(fn ($field) => $field instanceof Slug);

        $this->assertNotNull($slugField);
        $this->assertEquals('Slug', $slugField->name);
        $this->assertEquals('title', $slugField->fromAttribute);
        $this->assertContains('required', $slugField->rules);
        $this->assertContains('alpha_dash', $slugField->rules);
    }

    /** @test */
    public function it_has_content_field_with_markdown_editor(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $contentField = collect($fields)->first(fn ($field) => $field instanceof Markdown);

        $this->assertNotNull($contentField);
        $this->assertEquals('Content', $contentField->name);
        $this->assertTrue($contentField->showToolbar);
        $this->assertTrue($contentField->enableSlashCommands);
        $this->assertContains('required', $contentField->rules);
    }

    /** @test */
    public function it_has_status_field_with_correct_options(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $statusField = collect($fields)->first(fn ($field) => $field instanceof Select && $field->attribute === 'status'
        );

        $this->assertNotNull($statusField);
        $this->assertEquals('Status', $statusField->name);

        $expectedOptions = [
            'draft' => 'Draft',
            'published' => 'Published',
            'scheduled' => 'Scheduled',
            'archived' => 'Archived',
        ];

        $this->assertEquals($expectedOptions, $statusField->options);
        $this->assertEquals('draft', $statusField->default);
    }

    /** @test */
    public function it_has_featured_image_field_with_media_library(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        $imageField = collect($fields)->first(fn ($field) => $field instanceof MediaLibraryImage);

        $this->assertNotNull($imageField);
        $this->assertEquals('Featured Image', $imageField->name);
        $this->assertEquals('featured_images', $imageField->collection);
        $this->assertTrue($imageField->singleFile);
        $this->assertEquals(5120, $imageField->maxFileSize); // 5MB
    }

    /** @test */
    public function it_can_create_blog_post_through_resource(): void
    {
        $category = BlogCategory::factory()->create();
        $tag = BlogTag::factory()->create();

        $data = [
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'content' => '# Test Content\n\nThis is a test blog post.',
            'excerpt' => 'Test excerpt',
            'status' => 'draft',
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test meta description',
            'categories' => [$category->id],
            'tags' => [$tag->id],
        ];

        $request = new Request($data);
        $resource = new BlogPostResource;

        // Simulate resource creation
        $model = new BlogPost;
        foreach ($resource->fields($request) as $field) {
            $field->fill($request, $model);
        }

        $model->save();

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'status' => 'draft',
        ]);
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
        $titleField = $requiredFields->first(fn ($field) => $field->attribute === 'title');
        $contentField = $requiredFields->first(fn ($field) => $field->attribute === 'content');

        $this->assertNotNull($titleField);
        $this->assertNotNull($contentField);
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
    }

    /** @test */
    public function it_has_relationship_fields(): void
    {
        $request = new Request;
        $fields = $this->resource->fields($request);

        // Check for ManyToMany fields (categories and tags)
        $relationshipFields = collect($fields)->filter(fn ($field) => $field instanceof ManyToMany &&
            in_array($field->attribute, ['categories', 'tags'])
        );

        $this->assertGreaterThanOrEqual(2, $relationshipFields->count());
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
}
