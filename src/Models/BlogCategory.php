<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * BlogCategory Model
 *
 * Represents a blog category with hierarchical support, slug generation,
 * and SEO fields. Categories can have parent-child relationships.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property int $sort_order
 * @property bool $is_active
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'blog_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BlogCategory $category) {
            if (empty($category->slug)) {
                $category->slug = $category->generateUniqueSlug($category->name);
            }
        });

        static::updating(function (BlogCategory $category) {
            if ($category->isDirty('name') && empty($category->getOriginal('slug'))) {
                $category->slug = $category->generateUniqueSlug($category->name);
            }

            // Prevent circular references
            if ($category->isDirty('parent_id') && $category->parent_id) {
                $category->validateParentRelationship();
            }
        });

        static::deleting(function (BlogCategory $category) {
            // Remove pivot relationships
            $category->posts()->detach();
        });
    }

    /**
     * Generate a unique slug from the given name.
     */
    protected function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Validate parent relationship to prevent circular references.
     */
    protected function validateParentRelationship(): void
    {
        if ($this->parent_id === $this->id) {
            throw new InvalidArgumentException('A category cannot be its own parent.');
        }

        // Check if the new parent is a descendant of this category
        $descendants = $this->getAllDescendants();
        if ($descendants->contains('id', $this->parent_id)) {
            throw new InvalidArgumentException('Cannot create circular reference: the selected parent is a descendant of this category.');
        }
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(BlogCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the posts for the category.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_categories')
            ->withTimestamps();
    }

    /**
     * Get all descendants (children, grandchildren, etc.).
     */
    public function getAllDescendants(): Collection
    {
        $descendants = new Collection;

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    /**
     * Get all ancestors (parent, grandparent, etc.).
     */
    public function getAllAncestors(): Collection
    {
        $ancestors = new Collection;
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Check if this is a root category (has no parent).
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a leaf category (has no children).
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Get the depth level of this category in the hierarchy.
     */
    public function getDepth(): int
    {
        return $this->getAllAncestors()->count();
    }

    /**
     * Scope to get root categories (no parent).
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get categories ordered by sort_order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope to get categories with their children.
     */
    public function scopeWithChildren(Builder $query): Builder
    {
        return $query->with(['children' => function ($query) {
            $query->active()->ordered();
        }]);
    }

    /**
     * Get the full path of this category (including ancestors).
     */
    public function getFullPath(string $separator = ' > '): string
    {
        $ancestors = $this->getAllAncestors()->reverse();
        $path = $ancestors->pluck('name')->toArray();
        $path[] = $this->name;

        return implode($separator, $path);
    }

    /**
     * Get the URL path for this category.
     */
    public function getUrlPath(string $separator = '/'): string
    {
        $ancestors = $this->getAllAncestors()->reverse();
        $path = $ancestors->pluck('slug')->toArray();
        $path[] = $this->slug;

        return implode($separator, $path);
    }
}
