<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Blog Post Activity Model
 *
 * Tracks activities and changes made to blog posts for auditing
 * and analytics purposes.
 *
 * @property int $id
 * @property int $blog_post_id
 * @property string $action
 * @property string $description
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read BlogPost $blogPost
 * @property-read \App\Models\User|null $user
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPostActivity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'blog_post_activities';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'blog_post_id',
        'action',
        'description',
        'user_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the blog post that this activity belongs to.
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    /**
     * Get the user who performed this activity.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        return $this->belongsTo($userModel);
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get activities for publishing-related actions.
     */
    public function scopePublishingActivities($query)
    {
        return $query->whereIn('action', [
            'published',
            'unpublished',
            'scheduled',
            'rescheduled',
            'bulk_published',
            'bulk_scheduled',
        ]);
    }

    /**
     * Get the formatted description with user context.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $description = $this->description;

        if ($this->user) {
            $description .= " by {$this->user->name}";
        } elseif ($this->user_agent === 'System/ScheduledJob') {
            $description .= ' (automatic)';
        }

        return $description;
    }

    /**
     * Get the activity icon based on action type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'published', 'bulk_published' => 'check-circle',
            'unpublished' => 'x-circle',
            'scheduled', 'bulk_scheduled' => 'clock',
            'rescheduled' => 'calendar',
            'draft_saved' => 'edit',
            'preview_accessed' => 'eye',
            'publish_failed' => 'alert-circle',
            default => 'activity',
        };
    }

    /**
     * Get the activity color based on action type.
     */
    public function getColorAttribute(): string
    {
        return match ($this->action) {
            'published', 'bulk_published' => 'green',
            'unpublished' => 'red',
            'scheduled', 'bulk_scheduled', 'rescheduled' => 'blue',
            'draft_saved' => 'gray',
            'preview_accessed' => 'purple',
            'publish_failed' => 'red',
            default => 'gray',
        };
    }
}
