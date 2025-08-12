<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Services;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogPostActivity;

/**
 * Draft Preview Service
 *
 * Handles secure preview functionality for draft and scheduled posts,
 * allowing users to preview unpublished content before publication.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class DraftPreviewService
{
    /**
     * Generate a secure preview token for a post.
     */
    public function generatePreviewToken(BlogPost $post): string
    {
        $payload = [
            'post_id' => $post->id,
            'post_updated_at' => $post->updated_at->timestamp,
            'expires_at' => now()->addHours(config('cms-blog-system.preview.token_expiry', 24))->timestamp,
            'random' => Str::random(16),
        ];

        $token = hash('sha256', json_encode($payload).config('app.key'));

        // Store token in cache for validation
        $this->storePreviewToken($post, $token, $payload['expires_at']);

        return $token;
    }

    /**
     * Generate a preview URL for a post.
     */
    public function generatePreviewUrl(BlogPost $post): string
    {
        $token = $this->generatePreviewToken($post);

        return route('blog.preview', [
            'post' => $post->id,
            'token' => $token,
        ]);
    }

    /**
     * Validate a preview token for a post.
     */
    public function validatePreviewToken(BlogPost $post, string $token): bool
    {
        $cacheKey = $this->getTokenCacheKey($post, $token);
        $tokenData = Cache::get($cacheKey);

        if (! $tokenData) {
            return false;
        }

        // Check if token has expired
        if (now()->timestamp > $tokenData['expires_at']) {
            Cache::forget($cacheKey);

            return false;
        }

        // Validate token integrity
        $expectedPayload = [
            'post_id' => $post->id,
            'post_updated_at' => $post->updated_at->timestamp,
            'expires_at' => $tokenData['expires_at'],
            'random' => $tokenData['random'],
        ];

        $expectedToken = hash('sha256', json_encode($expectedPayload).config('app.key'));

        return hash_equals($expectedToken, $token);
    }

    /**
     * Render a preview of a post.
     */
    public function renderPreview(int $postId, string $token): Response
    {
        $post = BlogPost::find($postId);

        if (! $post) {
            abort(404, 'Post not found');
        }

        // If post is published, redirect to public URL
        if ($post->isPublished()) {
            return redirect()->route('blog.show', $post->slug);
        }

        // Validate preview token
        if (! $this->validatePreviewToken($post, $token)) {
            abort(404, 'Invalid or expired preview token');
        }

        // Log preview access
        $this->logPreviewAccess($post);

        // Render preview with special styling
        return response()->view('cms-blog-system::blog.preview', [
            'post' => $post,
            'isPreview' => true,
            'previewType' => $post->status,
        ])->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Generate a shareable preview link with expiration.
     */
    public function generateShareablePreviewLink(BlogPost $post, Carbon $expiresAt): string
    {
        $payload = [
            'post_id' => $post->id,
            'expires_at' => $expiresAt->timestamp,
            'shareable' => true,
            'random' => Str::random(32),
        ];

        $token = Crypt::encryptString(json_encode($payload));

        return route('blog.preview.shareable', [
            'token' => urlencode($token),
        ]);
    }

    /**
     * Revoke a specific preview token.
     */
    public function revokePreviewToken(BlogPost $post, string $token): bool
    {
        $cacheKey = $this->getTokenCacheKey($post, $token);

        return Cache::forget($cacheKey);
    }

    /**
     * Revoke all preview tokens for a post.
     */
    public function revokeAllPreviewTokens(BlogPost $post): bool
    {
        $pattern = "preview_token:{$post->id}:*";

        // Get all keys matching the pattern
        $keys = Cache::getRedis()->keys($pattern);

        if (empty($keys)) {
            return true;
        }

        // Delete all matching keys
        return Cache::getRedis()->del($keys) > 0;
    }

    /**
     * Store preview token in cache.
     */
    protected function storePreviewToken(BlogPost $post, string $token, int $expiresAt): void
    {
        $cacheKey = $this->getTokenCacheKey($post, $token);
        $ttl = $expiresAt - now()->timestamp;

        Cache::put($cacheKey, [
            'post_id' => $post->id,
            'expires_at' => $expiresAt,
            'random' => Str::random(16),
            'created_at' => now()->timestamp,
        ], $ttl);
    }

    /**
     * Get cache key for a preview token.
     */
    protected function getTokenCacheKey(BlogPost $post, string $token): string
    {
        return "preview_token:{$post->id}:".substr($token, 0, 16);
    }

    /**
     * Log preview access for analytics and security.
     */
    protected function logPreviewAccess(BlogPost $post): void
    {
        try {
            if (class_exists(BlogPostActivity::class)) {
                BlogPostActivity::create([
                    'blog_post_id' => $post->id,
                    'action' => 'preview_accessed',
                    'description' => 'Draft preview accessed',
                    'user_id' => auth()->id(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            Log::info("Preview accessed for post {$post->id}", [
                'post_id' => $post->id,
                'post_title' => $post->title,
                'post_status' => $post->status,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log preview access: '.$e->getMessage());
        }
    }

    /**
     * Clean up expired preview tokens.
     */
    public function cleanupExpiredTokens(): int
    {
        $pattern = 'preview_token:*';
        $keys = Cache::getRedis()->keys($pattern);
        $deletedCount = 0;

        foreach ($keys as $key) {
            $tokenData = Cache::get($key);

            if ($tokenData && now()->timestamp > $tokenData['expires_at']) {
                Cache::forget($key);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Get preview statistics for a post.
     */
    public function getPreviewStats(BlogPost $post): array
    {
        if (! class_exists(BlogPostActivity::class)) {
            return [
                'total_previews' => 0,
                'unique_visitors' => 0,
                'last_preview' => null,
            ];
        }

        $activities = BlogPostActivity::where('blog_post_id', $post->id)
            ->where('action', 'preview_accessed')
            ->get();

        return [
            'total_previews' => $activities->count(),
            'unique_visitors' => $activities->pluck('ip_address')->unique()->count(),
            'last_preview' => $activities->max('created_at'),
        ];
    }
}
