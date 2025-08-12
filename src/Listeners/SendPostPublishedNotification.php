<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use JTD\CMSBlogSystem\Events\PostPublished;
use JTD\CMSBlogSystem\Notifications\PostPublishedNotification;

/**
 * Send Post Published Notification Listener
 *
 * Handles sending notifications when a post is published.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class SendPostPublishedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->onQueue('notifications');
    }

    /**
     * Handle the event.
     */
    public function handle(PostPublished $event): void
    {
        // Send notification to post author if available
        if ($event->post->author) {
            $event->post->author->notify(new PostPublishedNotification($event->post));
        }

        // Send notification to administrators if configured
        $adminEmails = config('cms-blog-system.notifications.admin_emails', []);
        if (! empty($adminEmails)) {
            Notification::route('mail', $adminEmails)
                ->notify(new PostPublishedNotification($event->post));
        }

        // Send notification to subscribers if configured
        if (config('cms-blog-system.notifications.notify_subscribers', false)) {
            $this->notifySubscribers($event->post);
        }
    }

    /**
     * Notify blog subscribers about the new post.
     */
    protected function notifySubscribers($post): void
    {
        // This would integrate with a subscriber system
        // For now, we'll just log it
        \Log::info("Would notify subscribers about new post: {$post->title}");
    }
}
