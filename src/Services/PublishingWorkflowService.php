<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;
use JTD\CMSBlogSystem\Events\PostPublished;
use JTD\CMSBlogSystem\Events\PostScheduled;
use JTD\CMSBlogSystem\Events\PostUnpublished;
use JTD\CMSBlogSystem\Jobs\PublishScheduledPost;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogPostActivity;

/**
 * Publishing Workflow Service
 *
 * Handles the complete publishing workflow for blog posts including
 * draft management, scheduling, publishing, and bulk operations.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PublishingWorkflowService
{
    /**
     * Save a post as draft.
     */
    public function saveDraft(BlogPost $post): bool
    {
        try {
            $post->status = 'draft';
            $post->published_at = null;
            $post->save();

            $this->logActivity($post, 'draft_saved', 'Post saved as draft');

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to save draft: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Publish a post immediately.
     */
    public function publishNow(BlogPost $post): bool
    {
        if (! $this->validatePostForPublishing($post)) {
            return false;
        }

        try {
            DB::transaction(function () use ($post) {
                $post->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);

                $this->logActivity($post, 'published', 'Post published immediately');

                Event::dispatch(new PostPublished($post, now(), auth()->user()));
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to publish post: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Schedule a post for future publication.
     */
    public function schedulePost(BlogPost $post, Carbon $publishDate): bool
    {
        if ($publishDate->isPast()) {
            throw new \InvalidArgumentException('Cannot schedule post for a past date');
        }

        if (! $this->validatePostForPublishing($post)) {
            return false;
        }

        try {
            DB::transaction(function () use ($post, $publishDate) {
                $post->update([
                    'status' => 'scheduled',
                    'published_at' => $publishDate,
                ]);

                $this->logActivity($post, 'scheduled', "Post scheduled for publication at {$publishDate->format('Y-m-d H:i:s')}");

                // Queue the publishing job
                PublishScheduledPost::dispatch($post)->delay($publishDate);

                Event::dispatch(new PostScheduled($post, $publishDate, auth()->user()));
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to schedule post: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Unpublish a published post.
     */
    public function unpublish(BlogPost $post): bool
    {
        try {
            DB::transaction(function () use ($post) {
                $post->update([
                    'status' => 'draft',
                    'published_at' => null,
                ]);

                $this->logActivity($post, 'unpublished', 'Post unpublished and reverted to draft');

                Event::dispatch(new PostUnpublished($post, now(), auth()->user()));
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to unpublish post: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Reschedule a scheduled post.
     */
    public function reschedulePost(BlogPost $post, Carbon $newPublishDate): bool
    {
        if ($newPublishDate->isPast()) {
            throw new \InvalidArgumentException('Cannot reschedule post for a past date');
        }

        if ($post->status !== 'scheduled') {
            throw new \InvalidArgumentException('Can only reschedule posts that are currently scheduled');
        }

        try {
            DB::transaction(function () use ($post, $newPublishDate) {
                $oldDate = $post->published_at;

                $post->update([
                    'published_at' => $newPublishDate,
                ]);

                $this->logActivity($post, 'rescheduled', "Post rescheduled from {$oldDate->format('Y-m-d H:i:s')} to {$newPublishDate->format('Y-m-d H:i:s')}");

                // Re-queue the publishing job with new delay
                PublishScheduledPost::dispatch($post)->delay($newPublishDate);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to reschedule post: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Bulk publish multiple posts.
     */
    public function bulkPublish(array $postIds): bool
    {
        try {
            $posts = BlogPost::whereIn('id', $postIds)->get();
            $publishedCount = 0;

            DB::transaction(function () use ($posts, &$publishedCount) {
                foreach ($posts as $post) {
                    if ($this->validatePostForPublishing($post)) {
                        $post->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ]);

                        $this->logActivity($post, 'bulk_published', 'Post published via bulk operation');
                        Event::dispatch(new PostPublished($post, now(), auth()->user()));

                        $publishedCount++;
                    }
                }
            });

            \Log::info("Bulk published {$publishedCount} posts");

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to bulk publish posts: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Bulk schedule multiple posts.
     */
    public function bulkSchedule(array $postIds, Carbon $publishDate): bool
    {
        if ($publishDate->isPast()) {
            throw new \InvalidArgumentException('Cannot schedule posts for a past date');
        }

        try {
            $posts = BlogPost::whereIn('id', $postIds)->get();
            $scheduledCount = 0;

            DB::transaction(function () use ($posts, $publishDate, &$scheduledCount) {
                foreach ($posts as $post) {
                    if ($this->validatePostForPublishing($post)) {
                        $post->update([
                            'status' => 'scheduled',
                            'published_at' => $publishDate,
                        ]);

                        $this->logActivity($post, 'bulk_scheduled', "Post scheduled via bulk operation for {$publishDate->format('Y-m-d H:i:s')}");

                        // Queue the publishing job
                        PublishScheduledPost::dispatch($post)->delay($publishDate);

                        Event::dispatch(new PostScheduled($post, $publishDate, auth()->user()));

                        $scheduledCount++;
                    }
                }
            });

            \Log::info("Bulk scheduled {$scheduledCount} posts");

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to bulk schedule posts: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get posts that are ready for publishing.
     */
    public function getPostsReadyForPublishing(): Collection
    {
        return BlogPost::where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();
    }

    /**
     * Process scheduled posts that are ready for publishing.
     */
    public function processScheduledPosts(): int
    {
        $readyPosts = $this->getPostsReadyForPublishing();
        $processedCount = 0;

        foreach ($readyPosts as $post) {
            if ($this->publishNow($post)) {
                $processedCount++;
            }
        }

        return $processedCount;
    }

    /**
     * Get publishing history for a post.
     */
    public function getPublishingHistory(BlogPost $post): array
    {
        if (! class_exists(BlogPostActivity::class)) {
            return [];
        }

        return BlogPostActivity::where('blog_post_id', $post->id)
            ->whereIn('action', ['published', 'unpublished', 'scheduled', 'rescheduled'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                return [
                    'status' => $this->mapActionToStatus($activity->action),
                    'timestamp' => $activity->created_at,
                    'description' => $activity->description,
                    'user_id' => $activity->user_id,
                ];
            })
            ->toArray();
    }

    /**
     * Validate a post before publishing.
     */
    protected function validatePostForPublishing(BlogPost $post): bool
    {
        $validator = Validator::make($post->toArray(), [
            'title' => 'required|string|min:1|max:255',
            'content' => 'required|string|min:1',
            'slug' => 'required|string|min:1',
        ]);

        return $validator->passes();
    }

    /**
     * Log activity for a post.
     */
    protected function logActivity(BlogPost $post, string $action, string $description): void
    {
        if (! class_exists(BlogPostActivity::class)) {
            return;
        }

        BlogPostActivity::create([
            'blog_post_id' => $post->id,
            'action' => $action,
            'description' => $description,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Map activity action to status.
     */
    protected function mapActionToStatus(string $action): string
    {
        return match ($action) {
            'published', 'bulk_published' => 'published',
            'unpublished' => 'draft',
            'scheduled', 'bulk_scheduled', 'rescheduled' => 'scheduled',
            default => $action,
        };
    }
}
