<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\TestHelpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;
use JTD\CMSBlogSystem\Tests\TestCase;
use JTD\CMSBlogSystem\Tests\TestHelpers\DatabaseTestingTrait;

/**
 * Database Testing Trait Test
 *
 * Tests the DatabaseTestingTrait utility methods.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class DatabaseTestingTraitTest extends TestCase
{
    use DatabaseTestingTrait, RefreshDatabase;

    /** @test */
    public function it_can_assert_table_exists(): void
    {
        // Should not throw exception for existing table
        $this->assertTableExists('blog_posts');

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_non_existent_table(): void
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage("Table 'non_existent_table' should exist");

        $this->assertTableExists('non_existent_table');
    }

    /** @test */
    public function it_can_assert_table_has_columns(): void
    {
        // Should not throw exception for existing columns
        $this->assertTableHasColumns('blog_posts', [
            'id', 'title', 'slug', 'content', 'status', 'created_at', 'updated_at',
        ]);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_missing_column(): void
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage("Table 'blog_posts' should have column 'non_existent_column'");

        $this->assertTableHasColumns('blog_posts', ['non_existent_column']);
    }

    /** @test */
    public function it_can_get_database_connection(): void
    {
        $connection = $this->getDatabaseConnection();

        $this->assertIsString($connection);
        $this->assertNotEmpty($connection);
    }

    /** @test */
    public function it_can_check_if_using_sqlite(): void
    {
        $isUsingSqlite = $this->isUsingSqlite();

        $this->assertIsBool($isUsingSqlite);
        // In testing, we're typically using SQLite
        $this->assertTrue($isUsingSqlite);
    }

    /** @test */
    public function it_can_seed_test_data(): void
    {
        $ecosystem = $this->seedTestData();

        $this->assertIsArray($ecosystem);
        $this->assertArrayHasKey('categories', $ecosystem);
        $this->assertArrayHasKey('tags', $ecosystem);
        $this->assertArrayHasKey('posts', $ecosystem);

        // Check that data was actually created
        $this->assertGreaterThan(0, $ecosystem['categories']->count());
        $this->assertGreaterThan(0, $ecosystem['tags']->count());
        $this->assertGreaterThan(0, $ecosystem['posts']->count());
    }

    /** @test */
    public function it_can_assert_model_crud(): void
    {
        $createData = [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'description' => 'Test description',
            'usage_count' => 0,
        ];

        $updateData = [
            'name' => 'Updated Tag',
            'usage_count' => 5,
        ];

        // Should not throw exception
        $this->assertModelCrud(BlogTag::class, $createData, $updateData);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_can_clean_up_test_data(): void
    {
        // Create some test data
        BlogPost::factory()->count(5)->create();

        $this->assertGreaterThan(0, BlogPost::count());

        // Clean up
        $this->cleanupTestData(['blog_posts']);

        $this->assertEquals(0, BlogPost::count());
    }

    /** @test */
    public function it_can_assert_optimized_queries(): void
    {
        // Create test data
        BlogPost::factory()->count(3)->create();

        // This should use only a few queries
        $this->assertOptimizedQueries(function () {
            BlogPost::all();
        }, 5);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function it_throws_exception_for_too_many_queries(): void
    {
        // Create test data
        BlogPost::factory()->count(5)->create();

        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->expectExceptionMessage('Check for N+1 query problems');

        // This will trigger N+1 queries and should fail
        $this->assertOptimizedQueries(function () {
            $posts = BlogPost::all();
            foreach ($posts as $post) {
                // This would cause N+1 if categories weren't eager loaded
                $post->categories()->count();
            }
        }, 2); // Very low limit to trigger failure
    }

    /** @test */
    public function it_can_skip_test_for_specific_database(): void
    {
        // This should skip since we're using SQLite in tests
        $this->skipIfNotDatabase('mysql', 'This test requires MySQL');

        // This line should not be reached
        $this->fail('Test should have been skipped');
    }

    /** @test */
    public function it_does_not_skip_test_for_current_database(): void
    {
        // This should not skip since we're using SQLite
        $this->skipIfNotDatabase('sqlite');

        // This line should be reached
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_get_table_indexes(): void
    {
        $indexes = $this->getTableIndexes('blog_posts');

        $this->assertIsArray($indexes);
        // Should have at least the primary key index
        $this->assertNotEmpty($indexes);
    }

    /** @test */
    public function it_can_get_table_foreign_keys(): void
    {
        $foreignKeys = $this->getTableForeignKeys('blog_post_categories');

        $this->assertIsArray($foreignKeys);

        if (! empty($foreignKeys)) {
            $firstFk = $foreignKeys[0];
            $this->assertArrayHasKey('column', $firstFk);
            $this->assertArrayHasKey('referenced_table', $firstFk);
            $this->assertArrayHasKey('referenced_column', $firstFk);
        }
    }

    /** @test */
    public function cleanup_test_data_handles_non_existent_tables(): void
    {
        // Should not throw exception for non-existent tables
        $this->cleanupTestData(['non_existent_table']);

        $this->assertTrue(true); // Test passed if no exception
    }

    /** @test */
    public function cleanup_test_data_uses_default_tables_when_empty(): void
    {
        // Create some test data
        BlogPost::factory()->create();

        $this->assertGreaterThan(0, BlogPost::count());

        // Clean up with default tables
        $this->cleanupTestData();

        $this->assertEquals(0, BlogPost::count());
    }

    /** @test */
    public function it_handles_foreign_key_constraints_during_cleanup(): void
    {
        // This test ensures that foreign key constraints are properly handled
        // during cleanup, even if we can't test the exact SQL in SQLite

        $ecosystem = $this->seedTestData();

        // Verify data exists
        $this->assertGreaterThan(0, $ecosystem['posts']->count());

        // Cleanup should work without foreign key constraint errors
        $this->cleanupTestData();

        $this->assertEquals(0, BlogPost::count());
    }
}
