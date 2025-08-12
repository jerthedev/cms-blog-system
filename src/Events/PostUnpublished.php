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
 * Post Unpublished Event
 *
 * Fired when a published blog post is unpublished and reverted to draft status.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PostUnpublished implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public BlogPost $post,
        public Carbon $unpublishedAt,
        public mixed $unpublishedBy = null
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
            'unpublished_at' => $this->unpublishedAt->toISOString(),
            'unpublished_by' => $this->unpublishedBy?->id,
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'post.unpublished';
    }
}
