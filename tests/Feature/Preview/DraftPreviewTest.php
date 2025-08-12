<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Preview;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Services\DraftPreviewService;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Draft Preview Test
 *
 * Tests the draft preview functionality allowing users to preview
 * unpublished posts before publishing.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class DraftPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected DraftPreviewService $previewService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previewService = app(DraftPreviewService::class);

        // Register preview routes for testing
        Route::get('/blog/preview/{post}/{token}', function ($postId, $token) {
            return $this->previewService->renderPreview($postId, $token);
        })->name('blog.preview');
    }

    /** @test */
    public function it_can_generate_preview_token_for_draft_post(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
            'title' => 'Draft Post for Preview',
            'content' => 'This is a draft post content.',
        ]);

        $token = $this->previewService->generatePreviewToken($post);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // SHA256 hash length
    }

    /** @test */
    public function it_can_generate_preview_url_for_draft_post(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $previewUrl = $this->previewService->generatePreviewUrl($post);

        $this->assertIsString($previewUrl);
        $this->assertStringContainsString('/blog/preview/', $previewUrl);
        $this->assertStringContainsString((string) $post->id, $previewUrl);
    }

    /** @test */
    public function it_can_validate_preview_token(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $validToken = $this->previewService->generatePreviewToken($post);
        $invalidToken = 'invalid-token-here';

        $this->assertTrue($this->previewService->validatePreviewToken($post, $validToken));
        $this->assertFalse($this->previewService->validatePreviewToken($post, $invalidToken));
    }

    /** @test */
    public function it_can_render_draft_preview(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
            'title' => 'Preview Test Post',
            'content' => '# Preview Content\n\nThis is preview content.',
        ]);

        $token = $this->previewService->generatePreviewToken($post);
        $response = $this->get("/blog/preview/{$post->id}/{$token}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee('Preview Test Post');
        $response->assertSee('This is preview content.');
        $response->assertSee('DRAFT PREVIEW'); // Preview indicator
    }

    /** @test */
    public function it_returns_404_for_invalid_preview_token(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $invalidToken = 'invalid-token';
        $response = $this->get("/blog/preview/{$post->id}/{$invalidToken}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_returns_404_for_non_existent_post(): void
    {
        $nonExistentPostId = 99999;
        $token = 'any-token';

        $response = $this->get("/blog/preview/{$nonExistentPostId}/{$token}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_can_preview_scheduled_posts(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'scheduled',
            'published_at' => now()->addWeek(),
            'title' => 'Scheduled Post Preview',
            'content' => 'This is scheduled content.',
        ]);

        $token = $this->previewService->generatePreviewToken($post);
        $response = $this->get("/blog/preview/{$post->id}/{$token}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee('Scheduled Post Preview');
        $response->assertSee('SCHEDULED PREVIEW'); // Scheduled indicator
    }

    /** @test */
    public function it_prevents_preview_of_published_posts_without_permission(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $token = $this->previewService->generatePreviewToken($post);
        $response = $this->get("/blog/preview/{$post->id}/{$token}");

        // Should redirect to public post URL instead
        $response->assertRedirect("/blog/{$post->slug}");
    }

    /** @test */
    public function preview_tokens_expire_after_configured_time(): void
    {
        config(['cms-blog-system.preview.token_expiry' => 1]); // 1 hour

        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $token = $this->previewService->generatePreviewToken($post);

        // Simulate time passing
        $this->travel(2)->hours();

        $this->assertFalse($this->previewService->validatePreviewToken($post, $token));
    }

    /** @test */
    public function it_can_revoke_preview_token(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $token = $this->previewService->generatePreviewToken($post);

        // Token should be valid initially
        $this->assertTrue($this->previewService->validatePreviewToken($post, $token));

        // Revoke the token
        $this->previewService->revokePreviewToken($post, $token);

        // Token should no longer be valid
        $this->assertFalse($this->previewService->validatePreviewToken($post, $token));
    }

    /** @test */
    public function it_can_revoke_all_preview_tokens_for_post(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $token1 = $this->previewService->generatePreviewToken($post);
        $token2 = $this->previewService->generatePreviewToken($post);

        // Both tokens should be valid initially
        $this->assertTrue($this->previewService->validatePreviewToken($post, $token1));
        $this->assertTrue($this->previewService->validatePreviewToken($post, $token2));

        // Revoke all tokens
        $this->previewService->revokeAllPreviewTokens($post);

        // Both tokens should no longer be valid
        $this->assertFalse($this->previewService->validatePreviewToken($post, $token1));
        $this->assertFalse($this->previewService->validatePreviewToken($post, $token2));
    }

    /** @test */
    public function preview_includes_draft_styling_and_indicators(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
            'title' => 'Draft with Styling',
        ]);

        $token = $this->previewService->generatePreviewToken($post);
        $response = $this->get("/blog/preview/{$post->id}/{$token}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee('draft-preview-banner'); // CSS class for draft banner
        $response->assertSee('This is a preview'); // Preview notice text
        $response->assertSee('noindex'); // SEO meta tag to prevent indexing
    }

    /** @test */
    public function it_logs_preview_access(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $token = $this->previewService->generatePreviewToken($post);
        $this->get("/blog/preview/{$post->id}/{$token}");

        // Check that preview access was logged
        $this->assertDatabaseHas('blog_post_activities', [
            'blog_post_id' => $post->id,
            'action' => 'preview_accessed',
            'description' => 'Draft preview accessed',
        ]);
    }

    /** @test */
    public function it_can_generate_shareable_preview_links(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
        ]);

        $shareableLink = $this->previewService->generateShareablePreviewLink($post, now()->addDays(7));

        $this->assertIsString($shareableLink);
        $this->assertStringContainsString('/blog/preview/', $shareableLink);

        // Should be accessible without authentication
        $response = $this->get($shareableLink);
        $response->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function preview_respects_post_visibility_settings(): void
    {
        $post = BlogPost::factory()->create([
            'status' => 'draft',
            'visibility' => 'private', // Assuming we have visibility settings
        ]);

        $token = $this->previewService->generatePreviewToken($post);

        // Should require authentication for private posts
        $response = $this->get("/blog/preview/{$post->id}/{$token}");

        if ($post->visibility === 'private') {
            $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        } else {
            $response->assertStatus(Response::HTTP_OK);
        }
    }
}
