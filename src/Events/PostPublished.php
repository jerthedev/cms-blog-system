<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JTD\CMSBlogSystem\Models\BlogPost;

/**
 * Post Published Event
 *
 * Fired when a blog post is published, either immediately or automatically
 * from a scheduled state.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PostPublished implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public BlogPost $post,
        public Carbon $publishedAt,
        public mixed $publishedBy = null
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            // Could broadcast to admin channels for real-time updates
            // new PrivateChannel('admin.posts'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug,
            'published_at' => $this->publishedAt->toISOString(),
            'published_by' => $this->publishedBy?->id,
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'post.published';
    }
}
