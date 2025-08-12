<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use JTD\AdminPanel\Fields\BelongsTo;
use JTD\AdminPanel\Fields\Boolean;
use JTD\AdminPanel\Fields\DateTime;
use JTD\AdminPanel\Fields\ID;
use JTD\AdminPanel\Fields\Number;
use JTD\AdminPanel\Fields\Slug;
use JTD\AdminPanel\Fields\Text;
use JTD\AdminPanel\Fields\Textarea;
use JTD\AdminPanel\Resources\Resource;
use JTD\CMSBlogSystem\Models\BlogCategory;

/**
 * BlogCategory AdminPanel Resource
 *
 * Comprehensive resource for managing blog categories with hierarchical support,
 * circular reference prevention, post count display, and SEO integration.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogCategoryResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    public static string $model = BlogCategory::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     */
    public static string $title = 'name';

    /**
     * The columns that should be searched.
     */
    public static array $search = ['name', 'description'];

    /**
     * The logical group associated with the resource.
     */
    public static ?string $group = 'Blog Management';

    /**
     * The priority of this resource in the group.
     */
    public static int $priority = 2;

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

            Text::make('Name')
                ->required()
                ->sortable()
                ->searchable()
                ->rules('required', 'max:255')
                ->help('The display name of the category'),

            Slug::make('Slug')
                ->from('name')
                ->rules([
                    'required',
                    'alpha_dash',
                    'max:100',
                    Rule::unique('blog_categories', 'slug')->ignore($this->resource->id ?? null),
                ])
                ->help('URL-friendly version of the category name'),

            Textarea::make('Description')
                ->nullable()
                ->rows(3)
                ->help('Optional description of the category')
                ->hideFromIndex(),

            BelongsTo::make('Parent Category', 'parent')
                ->resource(BlogCategoryResource::class)
                ->nullable()
                ->searchable()
                ->help('Select parent category for hierarchy (optional)')
                ->hideFromIndex(),

            Number::make('Post Count', 'posts_count')
                ->exceptOnForms()
                ->sortable()
                ->help('Number of posts in this category')
                ->resolveUsing(function ($value, $resource) {
                    return $resource->posts()->count();
                }),

            Boolean::make('Active', 'is_active')
                ->sortable()
                ->default(true)
                ->help('Whether this category is active and visible'),

            Number::make('Sort Order', 'sort_order')
                ->sortable()
                ->default(0)
                ->min(0)
                ->step(1)
                ->help('Order for displaying categories (lower numbers first)')
                ->hideFromIndex(),

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
            // TODO: Add category metrics cards
        ];
    }

    /**
     * Get the filters available for the resource.
     */
    public function filters(Request $request): array
    {
        return [
            // TODO: Add active status filter, parent category filter
        ];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(Request $request): array
    {
        return [
            // TODO: Add bulk activate/deactivate actions
        ];
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Blog Categories';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Blog Category';
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'blog-categories';
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
        $subtitle = $this->resource->is_active ? 'Active' : 'Inactive';

        if ($this->resource->parent) {
            $subtitle .= ' • Child of '.$this->resource->parent->name;
        } else {
            $subtitle .= ' • Root Category';
        }

        return $subtitle;
    }

    /**
     * Build an "index" query for the given resource.
     */
    public static function indexQuery(Request $request, $query)
    {
        return $query->withCount('posts')->with('parent');
    }

    /**
     * Build a "detail" query for the given resource.
     */
    public static function detailQuery(Request $request, $query)
    {
        return $query->withCount('posts')->with(['parent', 'children']);
    }

    /**
     * Build a "relatableQuery" for the given resource.
     */
    public static function relatableQuery(Request $request, $query)
    {
        return $query->where('is_active', true);
    }
}
