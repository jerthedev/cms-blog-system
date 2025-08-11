<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * BlogTag Model
 *
 * Represents a blog tag with usage tracking, color management,
 * and tag cloud functionality.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property int $usage_count
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTag extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'blog_tags';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'usage_count',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'is_active' => true,
        'usage_count' => 0,
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BlogTag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = $tag->generateUniqueSlug($tag->name);
            }
        });

        static::updating(function (BlogTag $tag) {
            if ($tag->isDirty('name') && empty($tag->getOriginal('slug'))) {
                $tag->slug = $tag->generateUniqueSlug($tag->name);
            }
        });

        static::deleting(function (BlogTag $tag) {
            // Remove pivot relationships
            $tag->posts()->detach();
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
     * Get the posts for the tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tags')
            ->withTimestamps();
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement the usage count (minimum 0).
     */
    public function decrementUsage(): void
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }

    /**
     * Reset the usage count to zero.
     */
    public function resetUsage(): void
    {
        $this->update(['usage_count' => 0]);
    }

    /**
     * Check if the tag has a valid hex color.
     */
    public function hasValidColor(): bool
    {
        if (empty($this->color)) {
            return false;
        }

        return preg_match('/^#[a-fA-F0-9]{6}$/', $this->color) === 1;
    }

    /**
     * Generate a random hex color for the tag.
     */
    public function generateRandomColor(): void
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
            '#F8C471', '#82E0AA', '#F1948A', '#85C1E9', '#D7BDE2',
        ];

        $this->update(['color' => $colors[array_rand($colors)]]);
    }

    /**
     * Calculate tag cloud weight based on usage count.
     */
    public function getTagCloudWeight(int $levels = 5): int
    {
        $maxUsage = static::max('usage_count') ?: 1;
        $minUsage = static::min('usage_count') ?: 0;

        if ($maxUsage === $minUsage) {
            return $levels;
        }

        $range = $maxUsage - $minUsage;
        $weight = (($this->usage_count - $minUsage) / $range) * ($levels - 1) + 1;

        return (int) round($weight);
    }

    /**
     * Scope to get active tags.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get popular tags (ordered by usage count).
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Scope to get tags with minimum usage count.
     */
    public function scopeWithMinimumUsage(Builder $query, int $minUsage): Builder
    {
        return $query->where('usage_count', '>=', $minUsage);
    }

    /**
     * Scope to get unused tags (usage count = 0).
     */
    public function scopeUnused(Builder $query): Builder
    {
        return $query->where('usage_count', 0);
    }

    /**
     * Scope to get tags suitable for tag cloud (active with usage > 0).
     */
    public function scopeForCloud(Builder $query): Builder
    {
        return $query->active()
            ->where('usage_count', '>', 0)
            ->orderBy('usage_count', 'desc');
    }

    /**
     * Find or create a tag by name.
     */
    public static function findOrCreateByName(string $name): static
    {
        return static::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name)]
        );
    }

    /**
     * Get tag cloud data with weights and colors.
     */
    public static function getTagCloudData(int $limit = 50): array
    {
        $tags = static::forCloud()->limit($limit)->get();

        return $tags->map(function (BlogTag $tag) {
            return [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'usage_count' => $tag->usage_count,
                'weight' => $tag->getTagCloudWeight(),
                'color' => $tag->color,
            ];
        })->toArray();
    }

    /**
     * Get popular tags for display.
     */
    public static function getPopularTags(int $limit = 10): Collection
    {
        return static::active()
            ->popular()
            ->withMinimumUsage(1)
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending tags (recently used frequently).
     */
    public static function getTrendingTags(int $days = 30, int $limit = 10): Collection
    {
        return static::active()
            ->where('updated_at', '>=', now()->subDays($days))
            ->popular()
            ->withMinimumUsage(1)
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up unused tags.
     */
    public static function cleanupUnused(int $olderThanDays = 30): int
    {
        return static::unused()
            ->where('created_at', '<', now()->subDays($olderThanDays))
            ->delete();
    }
}
