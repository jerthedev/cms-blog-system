<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * BlogPost Model
 *
 * Represents a blog post with publishing states, SEO fields, and media integration.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string $content
 * @property string $status
 * @property string|null $featured_image
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property Carbon|null $published_at
 * @property int|null $author_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPost extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The table associated with the model.
     */
    protected $table = 'blog_posts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at',
        'author_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = $post->generateUniqueSlug($post->title);
            }
        });

        static::updating(function (BlogPost $post) {
            if ($post->isDirty('title') && empty($post->getOriginal('slug'))) {
                $post->slug = $post->generateUniqueSlug($post->title);
            }
        });

        static::deleting(function (BlogPost $post) {
            // Decrement usage count for all tags before deleting
            $tagIds = $post->tags()->pluck('blog_tags.id')->toArray();
            if (! empty($tagIds)) {
                BlogTag::whereIn('id', $tagIds)
                    ->where('usage_count', '>', 0)
                    ->decrement('usage_count');
            }

            // Remove pivot relationships
            $post->categories()->detach();
            $post->tags()->detach();

            // Clear media relationships if media library is available
            if (static::hasMediaLibrarySupport()) {
                try {
                    $post->clearMediaCollection();
                } catch (\Exception $e) {
                    // Ignore media errors in testing environments
                }
            }
        });
    }

    /**
     * Generate a unique slug from the given title.
     */
    protected function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Check if the post is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->published_at !== null && $this->published_at->isFuture();
    }

    /**
     * Check if the post is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the post is archived.
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Publish the post.
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Schedule the post for future publication.
     */
    public function schedule(Carbon $publishDate): void
    {
        $this->update([
            'status' => 'scheduled',
            'published_at' => $publishDate,
        ]);
    }

    /**
     * Archive the post.
     */
    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
        ]);
    }

    /**
     * Get the post excerpt.
     */
    public function getExcerpt(int $length = 150): string
    {
        if (! empty($this->excerpt)) {
            return $this->excerpt;
        }

        // Strip markdown and HTML, then truncate
        $content = strip_tags($this->content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);

        if (strlen($content) <= $length) {
            return $content;
        }

        return substr($content, 0, $length);
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrl(): ?string
    {
        return $this->featured_image;
    }

    /**
     * Scope to get published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to get draft posts.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get scheduled posts.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '>', now());
    }

    /**
     * Scope to get archived posts.
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope to get posts in a specific category.
     */
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($categoryId) {
            $q->where('blog_categories.id', $categoryId);
        });
    }

    /**
     * Scope to get posts in a category by slug.
     */
    public function scopeInCategorySlug(Builder $query, string $slug): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($slug) {
            $q->where('blog_categories.slug', $slug);
        });
    }

    /**
     * Scope to get posts with a specific tag.
     */
    public function scopeWithTag(Builder $query, int $tagId): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($tagId) {
            $q->where('blog_tags.id', $tagId);
        });
    }

    /**
     * Scope to get posts with a tag by slug.
     */
    public function scopeWithTagSlug(Builder $query, string $slug): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($slug) {
            $q->where('blog_tags.slug', $slug);
        });
    }

    /**
     * Scope to get posts with all specified tags.
     */
    public function scopeWithAllTags(Builder $query, array $tagIds): Builder
    {
        foreach ($tagIds as $tagId) {
            $query->whereHas('tags', function (Builder $q) use ($tagId) {
                $q->where('blog_tags.id', $tagId);
            });
        }

        return $query;
    }

    /**
     * Scope to get posts with any of the specified tags.
     */
    public function scopeWithAnyTags(Builder $query, array $tagIds): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($tagIds) {
            $q->whereIn('blog_tags.id', $tagIds);
        });
    }

    /**
     * Scope to get recent posts.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get popular posts (ordered by created_at desc as proxy).
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to get featured posts (with featured image).
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->whereNotNull('featured_image');
    }

    /**
     * Scope to get posts published between dates.
     */
    public function scopePublishedBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get posts published in a specific year.
     */
    public function scopePublishedInYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('published_at', $year);
    }

    /**
     * Scope to get posts published in a specific month.
     */
    public function scopePublishedInMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('published_at', $year)
            ->whereMonth('published_at', $month);
    }

    /**
     * Scope to search posts by title and content.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to get posts with excerpt.
     */
    public function scopeWithExcerpt(Builder $query): Builder
    {
        return $query->whereNotNull('excerpt');
    }

    /**
     * Scope to get posts by author.
     */
    public function scopeByAuthor(Builder $query, int $authorId): Builder
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Scope to get posts related to a specific post.
     */
    public function scopeRelatedTo(Builder $query, int $postId): Builder
    {
        $post = static::with(['categories', 'tags'])->find($postId);

        if (! $post) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        $categoryIds = $post->categories->pluck('id')->toArray();
        $tagIds = $post->tags->pluck('id')->toArray();

        return $query->where('id', '!=', $postId)
            ->where(function (Builder $q) use ($categoryIds, $tagIds) {
                if (! empty($categoryIds)) {
                    $q->whereHas('categories', function (Builder $subQ) use ($categoryIds) {
                        $subQ->whereIn('blog_categories.id', $categoryIds);
                    });
                }

                if (! empty($tagIds)) {
                    $q->orWhereHas('tags', function (Builder $subQ) use ($tagIds) {
                        $subQ->whereIn('blog_tags.id', $tagIds);
                    });
                }
            })
            ->published()
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get archive data for blog posts.
     */
    public static function getArchiveData(): array
    {
        $posts = static::published()
            ->select('published_at')
            ->get()
            ->groupBy(function ($post) {
                return $post->published_at->format('Y-m');
            })
            ->map(function ($posts, $yearMonth) {
                [$year, $month] = explode('-', $yearMonth);
                $monthInt = (int) $month;

                return [
                    'year' => (int) $year,
                    'month' => $monthInt,
                    'month_name' => date('F', mktime(0, 0, 0, $monthInt, 1)),
                    'count' => $posts->count(),
                ];
            })
            ->sortByDesc(function ($item) {
                return $item['year'] * 100 + $item['month'];
            })
            ->values();

        return $posts->toArray();
    }

    /**
     * Get the categories for the blog post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_post_categories')
            ->withTimestamps();
    }

    /**
     * Get the tags for the blog post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tags')
            ->withTimestamps()
            ->withPivot([]);
    }

    /**
     * Override the attach method to increment tag usage.
     */
    public function attachTag($tag): void
    {
        $tagId = is_object($tag) ? $tag->id : $tag;

        if (! $this->tags()->where('blog_tags.id', $tagId)->exists()) {
            $this->tags()->attach($tagId);
            BlogTag::where('id', $tagId)->increment('usage_count');
        }
    }

    /**
     * Override the detach method to decrement tag usage.
     */
    public function detachTag($tag): void
    {
        $tagId = is_object($tag) ? $tag->id : $tag;

        if ($this->tags()->where('blog_tags.id', $tagId)->exists()) {
            $this->tags()->detach($tagId);
            BlogTag::where('id', $tagId)
                ->where('usage_count', '>', 0)
                ->decrement('usage_count');
        }
    }

    /**
     * Sync tags and update usage counts.
     */
    public function syncTags(array $tagIds): void
    {
        // Get current tags to calculate changes
        $currentTagIds = $this->tags()->pluck('blog_tags.id')->toArray();

        // Sync the tags
        $this->tags()->sync($tagIds);

        // Update usage counts
        $addedTags = array_diff($tagIds, $currentTagIds);
        $removedTags = array_diff($currentTagIds, $tagIds);

        // Increment usage for added tags
        if (! empty($addedTags)) {
            BlogTag::whereIn('id', $addedTags)->increment('usage_count');
        }

        // Decrement usage for removed tags
        if (! empty($removedTags)) {
            BlogTag::whereIn('id', $removedTags)
                ->where('usage_count', '>', 0)
                ->decrement('usage_count');
        }
    }

    /**
     * Check if media library support is available.
     */
    public static function hasMediaLibrarySupport(): bool
    {
        return Schema::hasTable('media') &&
               class_exists(\Spatie\MediaLibrary\MediaCollections\Models\Media::class);
    }

    /**
     * Get the media model class name.
     */
    public function getMediaModel(): string
    {
        return config('media-library.media_model', \Spatie\MediaLibrary\MediaCollections\Models\Media::class);
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_images')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('content_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(400)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(800)
            ->nonQueued();
    }
}
