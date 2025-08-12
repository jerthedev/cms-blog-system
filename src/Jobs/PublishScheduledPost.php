<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use JTD\CMSBlogSystem\Events\PostPublished;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogPostActivity;

/**
 * Publish Scheduled Post Job
 *
 * Automatically publishes scheduled posts when their publish date arrives.
 * This job is queued when a post is scheduled and executed at the specified time.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PublishScheduledPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    /**
     * The queue this job should be dispatched to.
     */
    public string $queue = 'default';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BlogPost $post
    ) {
        $this->onQueue('publishing');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if post still exists
            if (! $this->post->exists) {
                Log::info("Scheduled post {$this->post->id} no longer exists, skipping publication");

                return;
            }

            // Refresh the post to get latest data
            $this->post->refresh();

            // Only publish if post is still scheduled
            if ($this->post->status !== 'scheduled') {
                Log::info("Post {$this->post->id} is no longer scheduled (status: {$this->post->status}), skipping publication");

                return;
            }

            // Only publish if the scheduled time has passed
            if ($this->post->published_at && $this->post->published_at->isFuture()) {
                Log::info("Post {$this->post->id} scheduled time has not yet arrived, skipping publication");

                return;
            }

            // Validate post before publishing
            if (! $this->validatePostForPublishing()) {
                Log::error("Post {$this->post->id} failed validation, cannot publish");

                return;
            }

            // Publish the post
            $this->publishPost();

            Log::info("Successfully published scheduled post {$this->post->id}: {$this->post->title}");
        } catch (\Exception $e) {
            Log::error("Failed to publish scheduled post {$this->post->id}: ".$e->getMessage(), [
                'post_id' => $this->post->id,
                'exception' => $e,
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Scheduled post publication job failed permanently for post {$this->post->id}", [
            'post_id' => $this->post->id,
            'exception' => $exception,
            'attempts' => $this->attempts,
        ]);

        // Log the failure activity
        $this->logActivity('publish_failed', "Automatic publication failed: {$exception->getMessage()}");

        // Optionally, you could send a notification to administrators here
        // Notification::route('mail', config('cms-blog-system.admin_email'))
        //     ->notify(new ScheduledPostFailedNotification($this->post, $exception));
    }

    /**
     * Publish the post.
     */
    protected function publishPost(): void
    {
        DB::transaction(function () {
            // Update post status and published_at timestamp
            $this->post->update([
                'status' => 'published',
                'published_at' => now(), // Use actual publish time, not scheduled time
            ]);

            // Log the activity
            $this->logActivity('published', 'Post automatically published from scheduled status');

            // Fire the published event
            Event::dispatch(new PostPublished($this->post, now(), null));
        });
    }

    /**
     * Validate post before publishing.
     */
    protected function validatePostForPublishing(): bool
    {
        // Check required fields
        if (empty($this->post->title) || empty($this->post->content) || empty($this->post->slug)) {
            return false;
        }

        // Check if slug is unique among published posts
        $duplicateSlug = BlogPost::where('slug', $this->post->slug)
            ->where('status', 'published')
            ->where('id', '!=', $this->post->id)
            ->exists();

        if ($duplicateSlug) {
            Log::error("Cannot publish post {$this->post->id}: slug '{$this->post->slug}' already exists");

            return false;
        }

        return true;
    }

    /**
     * Log activity for the post.
     */
    protected function logActivity(string $action, string $description): void
    {
        if (! class_exists(BlogPostActivity::class)) {
            return;
        }

        try {
            BlogPostActivity::create([
                'blog_post_id' => $this->post->id,
                'action' => $action,
                'description' => $description,
                'user_id' => null, // System action
                'ip_address' => null,
                'user_agent' => 'System/ScheduledJob',
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to log activity for post {$this->post->id}: ".$e->getMessage());
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'publishing',
            'post:'.$this->post->id,
            'scheduled-publish',
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // Wait 30s, then 60s, then 120s between retries
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10); // Stop retrying after 10 minutes
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [
            // Add rate limiting if needed
            // new \Illuminate\Queue\Middleware\RateLimited('publishing'),
        ];
    }
}
