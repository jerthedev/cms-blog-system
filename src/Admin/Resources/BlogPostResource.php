<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use JTD\AdminPanel\Fields\DateTime;
use JTD\AdminPanel\Fields\ID;
use JTD\AdminPanel\Fields\ManyToMany;
use JTD\AdminPanel\Fields\Markdown;
use JTD\AdminPanel\Fields\MediaLibraryImage;
use JTD\AdminPanel\Fields\Select;
use JTD\AdminPanel\Fields\Slug;
use JTD\AdminPanel\Fields\Text;
use JTD\AdminPanel\Fields\Textarea;
use JTD\AdminPanel\Resources\Resource;
use JTD\CMSBlogSystem\Models\BlogPost;

/**
 * BlogPost AdminPanel Resource
 *
 * Comprehensive resource for managing blog posts with rich editing capabilities,
 * media integration, SEO fields, and publishing workflow.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPostResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    public static string $model = BlogPost::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     */
    public static string $title = 'title';

    /**
     * The columns that should be searched.
     */
    public static array $search = ['title', 'content', 'excerpt'];

    /**
     * The logical group associated with the resource.
     */
    public static ?string $group = 'Blog Management';

    /**
     * The priority of this resource in the group.
     */
    public static int $priority = 1;

    /**
     * Indicates if the resource should be globally searchable.
     */
    public static bool $globallySearchable = true;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()
                ->sortable()
                ->copyable(),

            Text::make('Title')
                ->required()
                ->sortable()
                ->searchable()
                ->rules('required', 'max:255')
                ->help('The main title of your blog post'),

            Slug::make('Slug')
                ->from('title')
                ->rules([
                    'required',
                    'alpha_dash',
                    'max:100',
                    Rule::unique('blog_posts', 'slug')->ignore($this->resource->id ?? null),
                ])
                ->help('URL-friendly version of the title'),

            Markdown::make('Content')
                ->withToolbar()
                ->withSlashCommands()
                ->placeholder('Start writing your blog post...')
                ->height(500)
                ->rules('required', 'min:10')
                ->help('Main content of your blog post in Markdown format'),

            Textarea::make('Excerpt')
                ->nullable()
                ->maxlength(300)
                ->rows(3)
                ->help('Optional summary for the post (300 characters max)'),

            Select::make('Status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'scheduled' => 'Scheduled',
                    'archived' => 'Archived',
                ])
                ->default('draft')
                ->displayUsingLabels()
                ->rules('required', 'in:draft,published,scheduled,archived')
                ->help('Publishing status of the post'),

            DateTime::make('Published At')
                ->nullable()
                ->help('Leave empty for immediate publishing when status is set to published')
                ->hideFromIndex(),

            ManyToMany::make('Categories')
                ->searchable()
                ->help('Select one or more categories for this post'),

            ManyToMany::make('Tags')
                ->searchable()
                ->help('Select tags for this post'),

            // SEO Fields Section
            Text::make('Meta Title')
                ->nullable()
                ->maxlength(60)
                ->help('SEO title (60 characters max for optimal display)')
                ->hideFromIndex(),

            Textarea::make('Meta Description')
                ->nullable()
                ->maxlength(160)
                ->rows(2)
                ->help('SEO description (160 characters max for optimal display)')
                ->hideFromIndex(),

            Text::make('Meta Keywords')
                ->nullable()
                ->help('Comma-separated keywords for SEO')
                ->hideFromIndex(),

            // Media Library Integration
            MediaLibraryImage::make('Featured Image')
                ->collection('featured_images')
                ->singleFile()
                ->conversions([
                    'thumb' => ['width' => 300, 'height' => 200, 'fit' => 'crop'],
                    'medium' => ['width' => 600, 'height' => 400, 'fit' => 'contain'],
                    'large' => ['width' => 1200, 'height' => 800, 'quality' => 90],
                ])
                ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxFileSize(5120) // 5MB
                ->nullable()
                ->help('Upload a featured image for the post (JPEG, PNG, or WebP, max 5MB)')
                ->hideFromIndex(),

            // Timestamps
            DateTime::make('Created At')
                ->onlyOnDetail()
                ->sortable(),

            DateTime::make('Updated At')
                ->onlyOnDetail()
                ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     */
    public function cards(Request $request): array
    {
        return [
            // TODO: Add blog post metrics cards
        ];
    }

    /**
     * Get the filters available for the resource.
     */
    public function filters(Request $request): array
    {
        return [
            // TODO: Add status filter, category filter, date range filter
        ];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(Request $request): array
    {
        return [
            // TODO: Add bulk publish/unpublish actions
        ];
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Blog Posts';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Blog Post';
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'blog-posts';
    }

    /**
     * Determine if this resource is available for navigation.
     */
    public static function availableForNavigation(Request $request): bool
    {
        return true;
    }

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): ?string
    {
        return $this->resource->status.' • '.$this->resource->created_at->format('M j, Y');
    }
}
