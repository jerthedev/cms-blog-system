<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Publishing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use JTD\CMSBlogSystem\Events\PostPublished;
use JTD\CMSBlogSystem\Events\PostScheduled;
use JTD\CMSBlogSystem\Events\PostUnpublished;
use JTD\CMSBlogSystem\Jobs\PublishScheduledPost;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Services\PublishingWorkflowService;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Blog Publishing Workflow Test
 *
 * Tests the complete publishing workflow including draft management,
 * scheduled publishing, notifications, and bulk operations.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPublishingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected PublishingWorkflowService $publishingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publishingService = app(PublishingWorkflowService::class);
    }

    /** @test */
    public function it_can_save_post_as_draft(): void
    {
        $post = BlogPost::factory()->make([
            'title' => 'Draft Post',
            'content' => 'This is a draft post',
            'status' => 'draft',
        ]);

        $result = $this->publishingService->saveDraft($post);

        $this->assertTrue($result);
        $this->assertEquals('draft', $post->status);
        $this->assertNull($post->published_at);
        $this->assertTrue($post->isDraft());
    }

    /** @test */
    public function it_can_publish_post_immediately(): void
    {
        Event::fake();
        Notification::fake();

        $post = BlogPost::factory()->create([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $result = $this->publishingService->publishNow($post);

        $this->assertTrue($result);
        $this->assertEquals('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);
        $this->assertTrue($post->fresh()->isPublished());

        // Check that events were fired
        Event::assertDispatched(PostPublished::class, function ($event) use ($post) {
            return $event->post->id === $post->id;
        });
    }

    /** @test */
    public function it_can_schedule_post_for_future_publication(): void
    {
        Event::fake();
        Queue::fake();

        $post = BlogPost::factory()->create([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $scheduleDate = now()->addDays(3);
        $result = $this->publishingService->schedulePost($post, $scheduleDate);

        $this->assertTrue($result);
        $this->assertEquals('scheduled', $post->fresh()->status);
        $this->assertEquals($scheduleDate->format('Y-m-d H:i:s'), $post->fresh()->published_at->format('Y-m-d H:i:s'));
        $this->assertTrue($post->fresh()->isScheduled());

        // Check that scheduling event was fired
        Event::assertDispatched(PostScheduled::class, function ($event) use ($post) {
            return $event->post->id === $post->id;
        });

        // Check that job was queued
        Queue::assertPushed(PublishScheduledPost::class, function ($job) use ($post) {
            return $job->post->id === $post->id;
        });
    }

    /** @test */
    public function it_can_unpublish_a_published_post(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $result = $this->publishingService->unpublish($post);

        $this->assertTrue($result);
        $this->assertEquals('draft', $post->fresh()->status);
        $this->assertNull($post->fresh()->published_at);

        // Check that unpublish event was fired
        Event::assertDispatched(PostUnpublished::class, function ($event) use ($post) {
            return $event->post->id === $post->id;
        });
    }

    /** @test */
    public function it_can_reschedule_a_scheduled_post(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->addDay(),
        ]);

        $newScheduleDate = now()->addWeek();
        $result = $this->publishingService->reschedulePost($post, $newScheduleDate);

        $this->assertTrue($result);
        $this->assertEquals('scheduled', $post->fresh()->status);
        $this->assertEquals($newScheduleDate->format('Y-m-d H:i:s'), $post->fresh()->published_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_bulk_publish_multiple_posts(): void
    {
        Event::fake();

        $posts = BlogPost::factory()->count(3)->create([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $postIds = $posts->pluck('id')->toArray();
        $result = $this->publishingService->bulkPublish($postIds);

        $this->assertTrue($result);

        foreach ($posts as $post) {
            $this->assertEquals('published', $post->fresh()->status);
            $this->assertNotNull($post->fresh()->published_at);
        }

        // Check that events were fired for each post
        Event::assertDispatched(PostPublished::class, 3);
    }

    /** @test */
    public function it_can_bulk_schedule_multiple_posts(): void
    {
        Queue::fake();

        $posts = BlogPost::factory()->count(3)->create([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $postIds = $posts->pluck('id')->toArray();
        $scheduleDate = now()->addDays(2);
        $result = $this->publishingService->bulkSchedule($postIds, $scheduleDate);

        $this->assertTrue($result);

        foreach ($posts as $post) {
            $this->assertEquals('scheduled', $post->fresh()->status);
            $this->assertEquals($scheduleDate->format('Y-m-d H:i:s'), $post->fresh()->published_at->format('Y-m-d H:i:s'));
        }

        // Check that jobs were queued for each post
        Queue::assertPushed(PublishScheduledPost::class, 3);
    }

    /** @test */
    public function it_prevents_scheduling_in_the_past(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $pastDate = now()->subDay();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot schedule post for a past date');

        $this->publishingService->schedulePost($post, $pastDate);
    }

    /** @test */
    public function it_can_get_posts_ready_for_publishing(): void
    {
        // Create posts with different statuses and dates
        BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->subMinute(), // Ready to publish
        ]);

        BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->addHour(), // Not ready yet
        ]);

        BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(), // Already published
        ]);

        $readyPosts = $this->publishingService->getPostsReadyForPublishing();

        $this->assertCount(1, $readyPosts);
        $this->assertEquals('scheduled', $readyPosts->first()->status);
        $this->assertTrue($readyPosts->first()->published_at->isPast());
    }

    /** @test */
    public function it_can_process_scheduled_posts_for_publishing(): void
    {
        Event::fake();

        $readyPost = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->subMinute(),
        ]);

        $notReadyPost = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->addHour(),
        ]);

        $result = $this->publishingService->processScheduledPosts();

        $this->assertEquals(1, $result); // One post processed

        // Check that ready post was published
        $this->assertEquals('published', $readyPost->fresh()->status);

        // Check that not-ready post remains scheduled
        $this->assertEquals('scheduled', $notReadyPost->fresh()->status);

        // Check that event was fired
        Event::assertDispatched(PostPublished::class, 1);
    }

    /** @test */
    public function it_validates_post_before_publishing(): void
    {
        $invalidPost = BlogPost::factory()->create([
            'title' => '', // Invalid: empty title
            'content' => '', // Invalid: empty content
            'status' => 'draft',
        ]);

        $result = $this->publishingService->publishNow($invalidPost);

        $this->assertFalse($result);
        $this->assertEquals('draft', $invalidPost->fresh()->status);
    }

    /** @test */
    public function it_can_get_publishing_history_for_post(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        // Publish the post
        $this->publishingService->publishNow($post);

        // Unpublish the post
        $this->publishingService->unpublish($post);

        // Publish again
        $this->publishingService->publishNow($post);

        $history = $this->publishingService->getPublishingHistory($post);

        $this->assertCount(3, $history); // 3 status changes
        $this->assertEquals('published', $history[0]['status']);
        $this->assertEquals('draft', $history[1]['status']);
        $this->assertEquals('published', $history[2]['status']);
    }
}
