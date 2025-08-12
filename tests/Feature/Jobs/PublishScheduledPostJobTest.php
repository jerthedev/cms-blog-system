<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Jobs;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use JTD\CMSBlogSystem\Events\PostPublished;
use JTD\CMSBlogSystem\Jobs\PublishScheduledPost;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Publish Scheduled Post Job Test
 *
 * Tests the job that automatically publishes scheduled posts.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class PublishScheduledPostJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_publish_a_scheduled_post(): void
    {
        Event::fake();
        Notification::fake();

        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->subMinute(),
        ]);

        $job = new PublishScheduledPost($post);
        $job->handle();

        $this->assertEquals('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);

        // Check that event was fired
        Event::assertDispatched(PostPublished::class, function ($event) use ($post) {
            return $event->post->id === $post->id;
        });
    }

    /** @test */
    public function it_does_not_publish_post_if_not_scheduled(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $job = new PublishScheduledPost($post);
        $job->handle();

        $this->assertEquals('draft', $post->fresh()->status);
        $this->assertNull($post->fresh()->published_at);

        // Check that no event was fired
        Event::assertNotDispatched(PostPublished::class);
    }

    /** @test */
    public function it_does_not_publish_post_if_publish_date_is_future(): void
    {
        Event::fake();

        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->addHour(),
        ]);

        $job = new PublishScheduledPost($post);
        $job->handle();

        $this->assertEquals('scheduled', $post->fresh()->status);

        // Check that no event was fired
        Event::assertNotDispatched(PostPublished::class);
    }

    /** @test */
    public function it_handles_deleted_post_gracefully(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->subMinute(),
        ]);

        $postId = $post->id;
        $post->delete();

        $job = new PublishScheduledPost($post);

        // Should not throw exception
        $job->handle();

        $this->assertDatabaseMissing('blog_posts', ['id' => $postId]);
    }

    /** @test */
    public function it_can_be_serialized_and_unserialized(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->addHour(),
        ]);

        $job = new PublishScheduledPost($post);

        // Serialize and unserialize the job
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(PublishScheduledPost::class, $unserialized);
        $this->assertEquals($post->id, $unserialized->post->id);
    }

    /** @test */
    public function it_has_correct_queue_configuration(): void
    {
        $post = BlogPost::factory()->create();
        $job = new PublishScheduledPost($post);

        $this->assertEquals('default', $job->queue);
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
    }

    /** @test */
    public function it_can_handle_job_failure(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->subMinute(),
        ]);

        $job = new PublishScheduledPost($post);

        // Simulate a failure scenario by making the post invalid
        $post->update(['title' => '']); // Invalid title

        $job->handle();

        // Post should remain scheduled if publishing fails
        $this->assertEquals('scheduled', $post->fresh()->status);
    }

    /** @test */
    public function it_updates_published_at_timestamp_when_publishing(): void
    {
        $originalPublishTime = now()->subDay();

        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => $originalPublishTime,
        ]);

        // Travel to future to simulate job running later
        Carbon::setTestNow(now()->addHour());

        $job = new PublishScheduledPost($post);
        $job->handle();

        $this->assertEquals('published', $post->fresh()->status);

        // Published_at should be updated to actual publish time, not scheduled time
        $this->assertTrue($post->fresh()->published_at->greaterThan($originalPublishTime));
        $this->assertTrue($post->fresh()->published_at->equalTo(now()));

        Carbon::setTestNow(); // Reset time
    }

    /** @test */
    public function it_can_retry_failed_jobs(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->subMinute(),
        ]);

        $job = new PublishScheduledPost($post);

        // Simulate first attempt failure
        $job->attempts = 1;

        // Should be able to retry
        $this->assertTrue($job->attempts < $job->tries);

        $job->handle();

        $this->assertEquals('published', $post->fresh()->status);
    }

    /** @test */
    public function it_logs_publishing_activity(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->subMinute(),
        ]);

        $job = new PublishScheduledPost($post);
        $job->handle();

        // Check that activity was logged (assuming we have activity logging)
        $this->assertDatabaseHas('blog_post_activities', [
            'blog_post_id' => $post->id,
            'action' => 'published',
            'description' => 'Post automatically published from scheduled status',
        ]);
    }
}
