<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\TestHelpers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database Testing Trait
 *
 * Provides utilities for database testing including table assertions,
 * constraint checking, and test data cleanup.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
trait DatabaseTestingTrait
{
    /**
     * Assert that a database table exists.
     */
    protected function assertTableExists(string $tableName, string $message = ''): void
    {
        $this->assertTrue(
            Schema::hasTable($tableName),
            $message ?: "Table '{$tableName}' should exist"
        );
    }

    /**
     * Assert that a database table has the expected columns.
     */
    protected function assertTableHasColumns(string $tableName, array $columns, string $message = ''): void
    {
        $this->assertTableExists($tableName);

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn($tableName, $column),
                $message ?: "Table '{$tableName}' should have column '{$column}'"
            );
        }
    }

    /**
     * Assert that a database table has the expected indexes.
     */
    protected function assertTableHasIndexes(string $tableName, array $indexes, string $message = ''): void
    {
        $this->assertTableExists($tableName);

        $tableIndexes = $this->getTableIndexes($tableName);

        foreach ($indexes as $indexName) {
            $this->assertContains(
                $indexName,
                $tableIndexes,
                $message ?: "Table '{$tableName}' should have index '{$indexName}'"
            );
        }
    }

    /**
     * Assert that a foreign key constraint exists.
     */
    protected function assertForeignKeyExists(string $tableName, string $columnName, string $referencedTable, string $message = ''): void
    {
        $foreignKeys = $this->getTableForeignKeys($tableName);

        $found = collect($foreignKeys)->first(function ($fk) use ($columnName, $referencedTable) {
            return $fk['column'] === $columnName && $fk['referenced_table'] === $referencedTable;
        });

        $this->assertNotNull(
            $found,
            $message ?: "Foreign key constraint should exist: {$tableName}.{$columnName} -> {$referencedTable}"
        );
    }

    /**
     * Get all indexes for a table.
     */
    protected function getTableIndexes(string $tableName): array
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        if ($connection->getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$tableName})");

            return collect($indexes)->pluck('name')->toArray();
        }

        if ($connection->getDriverName() === 'mysql') {
            $indexes = DB::select("SHOW INDEX FROM {$tableName}");

            return collect($indexes)->pluck('Key_name')->unique()->toArray();
        }

        // PostgreSQL
        $indexes = DB::select("
            SELECT indexname
            FROM pg_indexes
            WHERE tablename = ? AND schemaname = 'public'
        ", [$tableName]);

        return collect($indexes)->pluck('indexname')->toArray();
    }

    /**
     * Get all foreign keys for a table.
     */
    protected function getTableForeignKeys(string $tableName): array
    {
        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'sqlite') {
            $foreignKeys = DB::select("PRAGMA foreign_key_list({$tableName})");

            return collect($foreignKeys)->map(function ($fk) {
                return [
                    'column' => $fk->from,
                    'referenced_table' => $fk->table,
                    'referenced_column' => $fk->to,
                ];
            })->toArray();
        }

        if ($connection->getDriverName() === 'mysql') {
            $database = $connection->getDatabaseName();
            $foreignKeys = DB::select('
                SELECT
                    COLUMN_NAME as column_name,
                    REFERENCED_TABLE_NAME as referenced_table,
                    REFERENCED_COLUMN_NAME as referenced_column
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
            ', [$database, $tableName]);

            return collect($foreignKeys)->map(function ($fk) {
                return [
                    'column' => $fk->column_name,
                    'referenced_table' => $fk->referenced_table,
                    'referenced_column' => $fk->referenced_column,
                ];
            })->toArray();
        }

        // PostgreSQL
        $foreignKeys = DB::select("
            SELECT
                kcu.column_name,
                ccu.table_name AS referenced_table,
                ccu.column_name AS referenced_column
            FROM information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
                ON ccu.constraint_name = tc.constraint_name
                AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_name = ?
        ", [$tableName]);

        return collect($foreignKeys)->map(function ($fk) {
            return [
                'column' => $fk->column_name,
                'referenced_table' => $fk->referenced_table,
                'referenced_column' => $fk->referenced_column,
            ];
        })->toArray();
    }

    /**
     * Clean up test data from specific tables.
     */
    protected function cleanupTestData(array $tables = []): void
    {
        if (empty($tables)) {
            $tables = ['blog_post_tags', 'blog_post_categories', 'blog_posts', 'blog_categories', 'blog_tags'];
        }

        $connection = Schema::getConnection();

        // Handle foreign key constraints based on database driver
        if ($connection->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($connection->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // Re-enable foreign key constraints
        if ($connection->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($connection->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        }
    }

    /**
     * Seed test data for a complete blog ecosystem.
     */
    protected function seedTestData(): array
    {
        return BlogTestHelpers::createBlogEcosystem([
            'categories_count' => 5,
            'tags_count' => 15,
            'posts_count' => 20,
            'published_ratio' => 0.8,
            'with_relationships' => true,
        ]);
    }

    /**
     * Assert that database queries are optimized (no N+1 queries).
     */
    protected function assertOptimizedQueries(callable $callback, int $maxQueries = 10): void
    {
        $queryCount = 0;

        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $callback();

        $this->assertLessThanOrEqual(
            $maxQueries,
            $queryCount,
            "Expected at most {$maxQueries} queries, but {$queryCount} were executed. Check for N+1 query problems."
        );
    }

    /**
     * Create a temporary table for testing.
     */
    protected function createTemporaryTable(string $tableName, callable $schemaCallback): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($schemaCallback) {
            $schemaCallback($table);
        });

        // Register for cleanup
        $this->beforeApplicationDestroyed(function () use ($tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::drop($tableName);
            }
        });
    }

    /**
     * Assert that a model can be created, updated, and deleted.
     */
    protected function assertModelCrud(string $modelClass, array $createData, array $updateData): void
    {
        // Create
        $model = $modelClass::create($createData);
        $this->assertInstanceOf($modelClass, $model);
        $this->assertTrue($model->exists);

        // Read
        $found = $modelClass::find($model->id);
        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);

        // Update
        $model->update($updateData);
        $model->refresh();

        foreach ($updateData as $key => $value) {
            $this->assertEquals($value, $model->$key);
        }

        // Delete
        $id = $model->id;
        $model->delete();

        $deleted = $modelClass::find($id);
        $this->assertNull($deleted);
    }

    /**
     * Assert that pivot relationships work correctly.
     */
    protected function assertPivotRelationship($parentModel, $childModel, string $relationshipName): void
    {
        // Attach
        $parentModel->$relationshipName()->attach($childModel->id);

        $this->assertTrue(
            $parentModel->$relationshipName()->where($childModel->getKeyName(), $childModel->id)->exists(),
            'Pivot relationship should exist after attach'
        );

        // Detach
        $parentModel->$relationshipName()->detach($childModel->id);

        $this->assertFalse(
            $parentModel->$relationshipName()->where($childModel->getKeyName(), $childModel->id)->exists(),
            'Pivot relationship should not exist after detach'
        );
    }

    /**
     * Get the current database connection name.
     */
    protected function getDatabaseConnection(): string
    {
        return config('database.default');
    }

    /**
     * Check if we're using SQLite for testing.
     */
    protected function isUsingSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }

    /**
     * Skip test if not using a specific database driver.
     */
    protected function skipIfNotDatabase(string $driver, string $reason = ''): void
    {
        if (Schema::getConnection()->getDriverName() !== $driver) {
            $this->markTestSkipped($reason ?: "Test requires {$driver} database");
        }
    }
}
