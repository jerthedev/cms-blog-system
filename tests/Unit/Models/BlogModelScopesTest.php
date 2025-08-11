<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Blog Model Scopes Test
 *
 * Tests advanced query scopes for BlogPost model including filtering,
 * sorting, and content discovery features.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogModelScopesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_scope_recent_posts(): void
    {
        $oldPost = BlogPost::factory()->published()->create([
            'created_at' => now()->subMonths(2),
        ]);

        $recentPost = BlogPost::factory()->published()->create([
            'created_at' => now()->subDays(5),
        ]);

        $recentPosts = BlogPost::recent(30)->get();

        $this->assertCount(1, $recentPosts);
        $this->assertTrue($recentPosts->contains($recentPost));
        $this->assertFalse($recentPosts->contains($oldPost));
    }

    /** @test */
    public function it_can_scope_popular_posts(): void
    {
        // Create posts with different view counts (simulated via created_at for testing)
        $post1 = BlogPost::factory()->published()->create(['title' => 'Popular Post']);
        $post2 = BlogPost::factory()->published()->create(['title' => 'Less Popular']);
        $post3 = BlogPost::factory()->published()->create(['title' => 'Unpopular Post']);

        // Simulate popularity by creation order (most recent = most popular for testing)
        $popularPosts = BlogPost::popular()->get();

        $this->assertCount(3, $popularPosts);
        // Should be ordered by created_at desc (our popularity proxy)
        $this->assertEquals($post3->id, $popularPosts->first()->id);
    }

    /** @test */
    public function it_can_scope_featured_posts(): void
    {
        $featuredPost = BlogPost::factory()->published()->create([
            'title' => 'Featured Post',
            'featured_image' => '/images/featured.jpg',
        ]);

        $regularPost = BlogPost::factory()->published()->create([
            'title' => 'Regular Post',
            'featured_image' => null,
        ]);

        $featuredPosts = BlogPost::featured()->get();

        $this->assertCount(1, $featuredPosts);
        $this->assertTrue($featuredPosts->contains($featuredPost));
        $this->assertFalse($featuredPosts->contains($regularPost));
    }

    /** @test */
    public function it_can_scope_posts_by_date_range(): void
    {
        $startDate = now()->subDays(10);
        $endDate = now()->subDays(5);

        $beforePost = BlogPost::factory()->published()->create([
            'published_at' => now()->subDays(15),
        ]);

        $inRangePost = BlogPost::factory()->published()->create([
            'published_at' => now()->subDays(7),
        ]);

        $afterPost = BlogPost::factory()->published()->create([
            'published_at' => now()->subDays(2),
        ]);

        $postsInRange = BlogPost::publishedBetween($startDate, $endDate)->get();

        $this->assertCount(1, $postsInRange);
        $this->assertTrue($postsInRange->contains($inRangePost));
        $this->assertFalse($postsInRange->contains($beforePost));
        $this->assertFalse($postsInRange->contains($afterPost));
    }

    /** @test */
    public function it_can_scope_posts_by_year(): void
    {
        $post2023 = BlogPost::factory()->published()->create([
            'published_at' => Carbon::create(2023, 6, 15),
        ]);

        $post2024 = BlogPost::factory()->published()->create([
            'published_at' => Carbon::create(2024, 6, 15),
        ]);

        $posts2023 = BlogPost::publishedInYear(2023)->get();
        $posts2024 = BlogPost::publishedInYear(2024)->get();

        $this->assertCount(1, $posts2023);
        $this->assertTrue($posts2023->contains($post2023));

        $this->assertCount(1, $posts2024);
        $this->assertTrue($posts2024->contains($post2024));
    }

    /** @test */
    public function it_can_scope_posts_by_month(): void
    {
        $postJune = BlogPost::factory()->published()->create([
            'published_at' => Carbon::create(2024, 6, 15),
        ]);

        $postJuly = BlogPost::factory()->published()->create([
            'published_at' => Carbon::create(2024, 7, 15),
        ]);

        $junePost = BlogPost::publishedInMonth(2024, 6)->get();
        $julyPosts = BlogPost::publishedInMonth(2024, 7)->get();

        $this->assertCount(1, $junePost);
        $this->assertTrue($junePost->contains($postJune));

        $this->assertCount(1, $julyPosts);
        $this->assertTrue($julyPosts->contains($postJuly));
    }

    /** @test */
    public function it_can_search_posts_by_title_and_content(): void
    {
        $post1 = BlogPost::factory()->published()->create([
            'title' => 'Laravel Framework Guide',
            'content' => 'This is about PHP development.',
        ]);

        $post2 = BlogPost::factory()->published()->create([
            'title' => 'JavaScript Basics',
            'content' => 'Learn Laravel integration with JavaScript.',
        ]);

        $post3 = BlogPost::factory()->published()->create([
            'title' => 'Python Tutorial',
            'content' => 'Python programming fundamentals.',
        ]);

        $laravelPosts = BlogPost::search('Laravel')->get();
        $phpPosts = BlogPost::search('PHP')->get();
        $pythonPosts = BlogPost::search('Python')->get();

        $this->assertCount(2, $laravelPosts);
        $this->assertTrue($laravelPosts->contains($post1));
        $this->assertTrue($laravelPosts->contains($post2));

        $this->assertCount(1, $phpPosts);
        $this->assertTrue($phpPosts->contains($post1));

        $this->assertCount(1, $pythonPosts);
        $this->assertTrue($pythonPosts->contains($post3));
    }

    /** @test */
    public function it_can_scope_posts_with_excerpt(): void
    {
        $postWithExcerpt = BlogPost::factory()->published()->create([
            'excerpt' => 'This is a custom excerpt.',
        ]);

        $postWithoutExcerpt = BlogPost::factory()->published()->create([
            'excerpt' => null,
        ]);

        $postsWithExcerpt = BlogPost::withExcerpt()->get();

        $this->assertCount(1, $postsWithExcerpt);
        $this->assertTrue($postsWithExcerpt->contains($postWithExcerpt));
        $this->assertFalse($postsWithExcerpt->contains($postWithoutExcerpt));
    }

    /** @test */
    public function it_can_scope_posts_by_author(): void
    {
        $author1Posts = BlogPost::factory()->count(2)->published()->create(['author_id' => 1]);
        $author2Posts = BlogPost::factory()->count(3)->published()->create(['author_id' => 2]);

        $author1Results = BlogPost::byAuthor(1)->get();
        $author2Results = BlogPost::byAuthor(2)->get();

        $this->assertCount(2, $author1Results);
        $this->assertCount(3, $author2Results);

        foreach ($author1Posts as $post) {
            $this->assertTrue($author1Results->contains($post));
        }

        foreach ($author2Posts as $post) {
            $this->assertTrue($author2Results->contains($post));
        }
    }

    /** @test */
    public function it_can_chain_multiple_scopes(): void
    {
        $category = BlogCategory::factory()->create();
        $tag = BlogTag::factory()->create();

        $matchingPost = BlogPost::factory()->published()->create([
            'title' => 'Laravel Tutorial',
            'featured_image' => '/image.jpg',
            'published_at' => now()->subDays(5),
        ]);

        $nonMatchingPost = BlogPost::factory()->draft()->create([
            'title' => 'Draft Post',
        ]);

        $matchingPost->categories()->attach($category->id);
        $matchingPost->tags()->attach($tag->id);

        $results = BlogPost::published()
            ->featured()
            ->recent(30)
            ->inCategory($category->id)
            ->withTag($tag->id)
            ->search('Laravel')
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($matchingPost));
        $this->assertFalse($results->contains($nonMatchingPost));
    }

    /** @test */
    public function it_can_get_related_posts(): void
    {
        $category = BlogCategory::factory()->create();
        $tag1 = BlogTag::factory()->create();
        $tag2 = BlogTag::factory()->create();

        $mainPost = BlogPost::factory()->published()->create(['title' => 'Main Post']);
        $relatedPost1 = BlogPost::factory()->published()->create(['title' => 'Related 1']);
        $relatedPost2 = BlogPost::factory()->published()->create(['title' => 'Related 2']);
        $unrelatedPost = BlogPost::factory()->published()->create(['title' => 'Unrelated']);

        // Set up relationships
        $mainPost->categories()->attach($category->id);
        $mainPost->tags()->attach([$tag1->id, $tag2->id]);

        $relatedPost1->categories()->attach($category->id);
        $relatedPost1->tags()->attach($tag1->id);

        $relatedPost2->tags()->attach($tag2->id);

        $relatedPosts = BlogPost::relatedTo($mainPost->id)->get();

        $this->assertGreaterThan(0, $relatedPosts->count());
        $this->assertFalse($relatedPosts->contains($mainPost)); // Should not include itself
        $this->assertTrue($relatedPosts->contains($relatedPost1));
        $this->assertTrue($relatedPosts->contains($relatedPost2));
        $this->assertFalse($relatedPosts->contains($unrelatedPost));
    }

    /** @test */
    public function it_can_get_archive_data(): void
    {
        // Create posts in different months
        BlogPost::factory()->published()->create([
            'published_at' => Carbon::create(2024, 1, 15),
        ]);

        BlogPost::factory()->count(2)->published()->create([
            'published_at' => Carbon::create(2024, 2, 15),
        ]);

        BlogPost::factory()->count(3)->published()->create([
            'published_at' => Carbon::create(2024, 3, 15),
        ]);

        $archiveData = BlogPost::getArchiveData();

        $this->assertIsArray($archiveData);
        $this->assertCount(3, $archiveData);

        // Check structure
        foreach ($archiveData as $archive) {
            $this->assertArrayHasKey('year', $archive);
            $this->assertArrayHasKey('month', $archive);
            $this->assertArrayHasKey('month_name', $archive);
            $this->assertArrayHasKey('count', $archive);
        }

        // Check counts
        $marchData = collect($archiveData)->firstWhere('month', 3);
        $this->assertEquals(3, $marchData['count']);
    }

    /** @test */
    public function scopes_work_with_pagination(): void
    {
        BlogPost::factory()->count(25)->published()->create();

        $paginatedPosts = BlogPost::published()->paginate(10);

        $this->assertEquals(25, $paginatedPosts->total());
        $this->assertEquals(10, $paginatedPosts->perPage());
        $this->assertEquals(3, $paginatedPosts->lastPage());
        $this->assertCount(10, $paginatedPosts->items());
    }
}
