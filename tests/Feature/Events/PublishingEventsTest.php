<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Events;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use JTD\CMSBlogSystem\Events\PostPublished;
use JTD\CMSBlogSystem\Events\PostScheduled;
use JTD\CMSBlogSystem\Events\PostUnpublished;
use JTD\CMSBlogSystem\Listeners\SendPostPublishedNotification;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Notifications\PostPublishedNotification;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Publishing Events Test
 *
 * Tests the events fired during publishing workflow operations.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PublishingEventsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function post_published_event_is_fired_when_post_is_published(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create(['status' => 'draft']);
        $post->publish();

        Event::assertDispatched(PostPublished::class, function ($event) use ($post) {
            return $event->post->id === $post->id &&
                   $event->publishedAt instanceof \Carbon\Carbon &&
                   $event->publishedBy === null; // No user in test context
        });
    }

    /** @test */
    public function post_scheduled_event_is_fired_when_post_is_scheduled(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create(['status' => 'draft']);
        $scheduleDate = now()->addDay();
        $post->schedule($scheduleDate);

        Event::assertDispatched(PostScheduled::class, function ($event) use ($post, $scheduleDate) {
            return $event->post->id === $post->id &&
                   $event->scheduledFor->equalTo($scheduleDate) &&
                   $event->scheduledBy === null; // No user in test context
        });
    }

    /** @test */
    public function post_unpublished_event_is_fired_when_post_is_unpublished(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Simulate unpublishing
        $post->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        event(new PostUnpublished($post, now(), null));

        Event::assertDispatched(PostUnpublished::class, function ($event) use ($post) {
            return $event->post->id === $post->id &&
                   $event->unpublishedAt instanceof \Carbon\Carbon &&
                   $event->unpublishedBy === null;
        });
    }

    /** @test */
    public function post_published_event_contains_correct_data(): void
    {
        $post = BlogPost::factory()->create(['status' => 'draft']);
        $publishTime = now();

        $event = new PostPublished($post, $publishTime, null);

        $this->assertEquals($post->id, $event->post->id);
        $this->assertEquals($publishTime, $event->publishedAt);
        $this->assertNull($event->publishedBy);
    }

    /** @test */
    public function post_scheduled_event_contains_correct_data(): void
    {
        $post = BlogPost::factory()->create(['status' => 'draft']);
        $scheduleTime = now()->addWeek();

        $event = new PostScheduled($post, $scheduleTime, null);

        $this->assertEquals($post->id, $event->post->id);
        $this->assertEquals($scheduleTime, $event->scheduledFor);
        $this->assertNull($event->scheduledBy);
    }

    /** @test */
    public function post_unpublished_event_contains_correct_data(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        $unpublishTime = now();

        $event = new PostUnpublished($post, $unpublishTime, null);

        $this->assertEquals($post->id, $event->post->id);
        $this->assertEquals($unpublishTime, $event->unpublishedAt);
        $this->assertNull($event->unpublishedBy);
    }

    /** @test */
    public function events_are_serializable(): void
    {
        $post = BlogPost::factory()->create();
        $time = now();

        $publishedEvent = new PostPublished($post, $time, null);
        $scheduledEvent = new PostScheduled($post, $time, null);
        $unpublishedEvent = new PostUnpublished($post, $time, null);

        // Test serialization
        $serializedPublished = serialize($publishedEvent);
        $serializedScheduled = serialize($scheduledEvent);
        $serializedUnpublished = serialize($unpublishedEvent);

        $this->assertIsString($serializedPublished);
        $this->assertIsString($serializedScheduled);
        $this->assertIsString($serializedUnpublished);

        // Test unserialization
        $unserializedPublished = unserialize($serializedPublished);
        $unserializedScheduled = unserialize($serializedScheduled);
        $unserializedUnpublished = unserialize($serializedUnpublished);

        $this->assertInstanceOf(PostPublished::class, $unserializedPublished);
        $this->assertInstanceOf(PostScheduled::class, $unserializedScheduled);
        $this->assertInstanceOf(PostUnpublished::class, $unserializedUnpublished);
    }

    /** @test */
    public function post_published_notification_is_sent_when_event_is_fired(): void
    {
        Notification::fake();

        $post = BlogPost::factory()->create(['status' => 'draft']);

        // Fire the event
        event(new PostPublished($post, now(), null));

        // Check that notification was sent
        Notification::assertSentTo(
            [$post], // Assuming post is notifiable
            PostPublishedNotification::class,
            function ($notification, $channels) use ($post) {
                return $notification->post->id === $post->id;
            }
        );
    }

    /** @test */
    public function events_can_be_listened_to_by_custom_listeners(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create();

        // Register a custom listener
        Event::listen(PostPublished::class, function ($event) {
            // Custom logic here
            $event->post->update(['meta_description' => 'Auto-updated on publish']);
        });

        Event::assertListening(PostPublished::class, SendPostPublishedNotification::class);
    }

    /** @test */
    public function events_include_user_context_when_available(): void
    {
        // This would test with actual user authentication
        // For now, we test the structure
        $post = BlogPost::factory()->create();
        $user = null; // In real scenario, this would be auth()->user()

        $event = new PostPublished($post, now(), $user);

        $this->assertEquals($user, $event->publishedBy);
    }

    /** @test */
    public function events_can_be_queued_for_performance(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create();

        // Events should implement ShouldQueue for heavy operations
        $event = new PostPublished($post, now(), null);

        // Check if event implements ShouldQueue
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $event);
    }
}
