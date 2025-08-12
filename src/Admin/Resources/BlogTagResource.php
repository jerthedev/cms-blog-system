<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use JTD\AdminPanel\Fields\Boolean;
use JTD\AdminPanel\Fields\DateTime;
use JTD\AdminPanel\Fields\ID;
use JTD\AdminPanel\Fields\Number;
use JTD\AdminPanel\Fields\Slug;
use JTD\AdminPanel\Fields\Text;
use JTD\AdminPanel\Fields\Textarea;
use JTD\AdminPanel\Resources\Resource;
use JTD\CMSBlogSystem\Models\BlogTag;

/**
 * BlogTag AdminPanel Resource
 *
 * Comprehensive resource for managing blog tags with usage statistics,
 * popularity sorting, bulk operations, and tag management features.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTagResource extends Resource
{
    /**
     * The model the resource corresponds to.
     */
    public static string $model = BlogTag::class;

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
    public static int $priority = 3;

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
                ->help('The display name of the tag'),

            Slug::make('Slug')
                ->from('name')
                ->rules([
                    'required',
                    'alpha_dash',
                    'max:100',
                    Rule::unique('blog_tags', 'slug')->ignore($this->resource->id ?? null),
                ])
                ->help('URL-friendly version of the tag name'),

            Textarea::make('Description')
                ->nullable()
                ->rows(2)
                ->help('Optional description of the tag')
                ->hideFromIndex(),

            Text::make('Color')
                ->nullable()
                ->placeholder('#FF6B6B')
                ->rules('nullable', 'regex:/^#[a-fA-F0-9]{6}$/')
                ->help('Hex color code for tag display (e.g., #FF6B6B)')
                ->hideFromIndex(),

            Number::make('Usage Count')
                ->exceptOnForms()
                ->sortable()
                ->help('Number of posts using this tag')
                ->resolveUsing(function ($value, $resource) {
                    return $resource->usage_count;
                }),

            Boolean::make('Active', 'is_active')
                ->sortable()
                ->default(true)
                ->help('Whether this tag is active and visible'),

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
            // TODO: Add tag metrics cards (total tags, popular tags, unused tags)
        ];
    }

    /**
     * Get the filters available for the resource.
     */
    public function filters(Request $request): array
    {
        return [
            // TODO: Add active status filter, usage count filter, color filter
        ];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(Request $request): array
    {
        return [
            // TODO: Add bulk activate/deactivate, merge tags, cleanup unused tags
        ];
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Blog Tags';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Blog Tag';
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'blog-tags';
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
        $subtitle .= ' • Used '.$this->resource->usage_count.' time'.($this->resource->usage_count !== 1 ? 's' : '');

        if ($this->resource->color) {
            $subtitle .= ' • '.$this->resource->color;
        }

        return $subtitle;
    }

    /**
     * Build an "index" query for the given resource.
     */
    public static function indexQuery(Request $request, $query)
    {
        // Default sort by usage count (popularity) descending
        return $query->orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc');
    }

    /**
     * Build a "detail" query for the given resource.
     */
    public static function detailQuery(Request $request, $query)
    {
        return $query->withCount('posts');
    }

    /**
     * Build a "relatableQuery" for the given resource.
     */
    public static function relatableQuery(Request $request, $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the value that should be displayed to represent the resource.
     */
    public function title(): string
    {
        $title = $this->resource->name;

        if ($this->resource->usage_count > 0) {
            $title .= ' ('.$this->resource->usage_count.')';
        }

        return $title;
    }
}
