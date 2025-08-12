<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use JTD\CMSBlogSystem\Models\BlogPost;

/**
 * Post Published Notification
 *
 * Notification sent when a blog post is published.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PostPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public BlogPost $post
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Blog Post Published: '.$this->post->title)
            ->greeting('Hello!')
            ->line('Your blog post has been published successfully.')
            ->line('**Post Title:** '.$this->post->title)
            ->line('**Published At:** '.$this->post->published_at->format('F j, Y \a\t g:i A'))
            ->action('View Post', route('blog.show', $this->post->slug))
            ->line('Thank you for using our blog system!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug,
            'published_at' => $this->post->published_at,
            'message' => "Your blog post '{$this->post->title}' has been published.",
            'action_url' => route('blog.show', $this->post->slug),
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(mixed $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
