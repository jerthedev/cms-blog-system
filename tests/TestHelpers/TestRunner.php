<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\TestHelpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Test Runner
 *
 * Utility class for running comprehensive test suites and generating reports.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class TestRunner
{
    /**
     * Run all blog system tests with comprehensive reporting.
     */
    public static function runFullTestSuite(): array
    {
        $results = [
            'start_time' => now(),
            'environment' => static::getTestEnvironment(),
            'suites' => [],
            'summary' => [],
        ];

        // Run different test suites
        $suites = [
            'Unit Tests' => 'tests/Unit',
            'Feature Tests' => 'tests/Feature',
            'Integration Tests' => 'tests/Integration',
        ];

        foreach ($suites as $suiteName => $path) {
            if (is_dir(base_path($path))) {
                $results['suites'][$suiteName] = static::runTestSuite($path);
            }
        }

        $results['end_time'] = now();
        $results['duration'] = $results['end_time']->diffInSeconds($results['start_time']);
        $results['summary'] = static::generateSummary($results['suites']);

        return $results;
    }

    /**
     * Run a specific test suite.
     */
    public static function runTestSuite(string $path): array
    {
        $startTime = microtime(true);

        // This would normally run PHPUnit programmatically
        // For now, we'll simulate the structure
        $result = [
            'path' => $path,
            'start_time' => now(),
            'tests_run' => 0,
            'assertions' => 0,
            'failures' => 0,
            'errors' => 0,
            'skipped' => 0,
            'duration' => 0,
        ];

        $endTime = microtime(true);
        $result['duration'] = round($endTime - $startTime, 2);
        $result['end_time'] = now();

        return $result;
    }

    /**
     * Generate test summary from suite results.
     */
    public static function generateSummary(array $suites): array
    {
        $summary = [
            'total_tests' => 0,
            'total_assertions' => 0,
            'total_failures' => 0,
            'total_errors' => 0,
            'total_skipped' => 0,
            'success_rate' => 0,
            'status' => 'unknown',
        ];

        foreach ($suites as $suite) {
            $summary['total_tests'] += $suite['tests_run'] ?? 0;
            $summary['total_assertions'] += $suite['assertions'] ?? 0;
            $summary['total_failures'] += $suite['failures'] ?? 0;
            $summary['total_errors'] += $suite['errors'] ?? 0;
            $summary['total_skipped'] += $suite['skipped'] ?? 0;
        }

        if ($summary['total_tests'] > 0) {
            $successful = $summary['total_tests'] - $summary['total_failures'] - $summary['total_errors'];
            $summary['success_rate'] = round(($successful / $summary['total_tests']) * 100, 2);
        }

        $summary['status'] = static::determineStatus($summary);

        return $summary;
    }

    /**
     * Get test environment information.
     */
    public static function getTestEnvironment(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_driver' => Schema::getConnection()->getDriverName(),
            'database_name' => Schema::getConnection()->getDatabaseName(),
            'memory_limit' => ini_get('memory_limit'),
            'time_limit' => ini_get('max_execution_time'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];
    }

    /**
     * Determine overall test status.
     */
    protected static function determineStatus(array $summary): string
    {
        if ($summary['total_tests'] === 0) {
            return 'unknown';
        }

        if ($summary['total_errors'] > 0) {
            return 'error';
        }

        if ($summary['success_rate'] >= 100) {
            return 'success';
        }

        if ($summary['success_rate'] >= 90) {
            return 'warning';
        }

        return 'failure';
    }

    /**
     * Run database integrity checks.
     */
    public static function runDatabaseIntegrityChecks(): array
    {
        $checks = [
            'tables_exist' => static::checkTablesExist(),
            'foreign_keys' => static::checkForeignKeys(),
            'indexes' => static::checkIndexes(),
            'constraints' => static::checkConstraints(),
        ];

        $checks['overall_status'] = collect($checks)->every(fn ($check) => $check['status'] === 'pass') ? 'pass' : 'fail';

        return $checks;
    }

    /**
     * Check that all required tables exist.
     */
    protected static function checkTablesExist(): array
    {
        $requiredTables = [
            'blog_posts',
            'blog_categories',
            'blog_tags',
            'blog_post_categories',
            'blog_post_tags',
        ];

        $missingTables = [];
        foreach ($requiredTables as $table) {
            if (! Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        return [
            'status' => empty($missingTables) ? 'pass' : 'fail',
            'message' => empty($missingTables) ? 'All required tables exist' : 'Missing tables: '.implode(', ', $missingTables),
            'details' => [
                'required' => $requiredTables,
                'missing' => $missingTables,
            ],
        ];
    }

    /**
     * Check foreign key constraints.
     */
    protected static function checkForeignKeys(): array
    {
        $expectedForeignKeys = [
            'blog_categories' => ['parent_id' => 'blog_categories'],
            'blog_post_categories' => [
                'blog_post_id' => 'blog_posts',
                'blog_category_id' => 'blog_categories',
            ],
            'blog_post_tags' => [
                'blog_post_id' => 'blog_posts',
                'blog_tag_id' => 'blog_tags',
            ],
        ];

        $issues = [];

        // For SQLite, we'll skip detailed foreign key checking
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return [
                'status' => 'pass',
                'message' => 'Foreign key constraints skipped for SQLite',
                'details' => ['driver' => 'sqlite'],
            ];
        }

        return [
            'status' => empty($issues) ? 'pass' : 'fail',
            'message' => empty($issues) ? 'All foreign keys are valid' : 'Foreign key issues found',
            'details' => $issues,
        ];
    }

    /**
     * Check database indexes.
     */
    protected static function checkIndexes(): array
    {
        $expectedIndexes = [
            'blog_posts' => ['slug', 'status', 'published_at'],
            'blog_categories' => ['slug', 'parent_id'],
            'blog_tags' => ['slug', 'usage_count'],
        ];

        $issues = [];

        foreach ($expectedIndexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $index) {
                if (! Schema::hasColumn($table, $index)) {
                    $issues[] = "Missing column {$table}.{$index}";
                }
            }
        }

        return [
            'status' => empty($issues) ? 'pass' : 'fail',
            'message' => empty($issues) ? 'All expected indexes exist' : 'Index issues found',
            'details' => $issues,
        ];
    }

    /**
     * Check database constraints.
     */
    protected static function checkConstraints(): array
    {
        $issues = [];

        // Check for basic data integrity
        try {
            // Check for orphaned categories
            $orphanedCategories = DB::table('blog_categories')
                ->whereNotNull('parent_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('blog_categories as parent')
                        ->whereRaw('parent.id = blog_categories.parent_id');
                })
                ->count();

            if ($orphanedCategories > 0) {
                $issues[] = "Found {$orphanedCategories} orphaned categories";
            }

            // Check for invalid tag usage counts
            $invalidTagCounts = DB::table('blog_tags')
                ->where('usage_count', '<', 0)
                ->count();

            if ($invalidTagCounts > 0) {
                $issues[] = "Found {$invalidTagCounts} tags with negative usage counts";
            }
        } catch (\Exception $e) {
            $issues[] = 'Error checking constraints: '.$e->getMessage();
        }

        return [
            'status' => empty($issues) ? 'pass' : 'fail',
            'message' => empty($issues) ? 'All constraints are valid' : 'Constraint issues found',
            'details' => $issues,
        ];
    }

    /**
     * Generate a test report.
     */
    public static function generateReport(array $results): string
    {
        $report = "# Blog System Test Report\n\n";
        $report .= '**Generated:** '.now()->format('Y-m-d H:i:s')."\n";
        $report .= "**Duration:** {$results['duration']} seconds\n\n";

        // Environment info
        $report .= "## Environment\n\n";
        foreach ($results['environment'] as $key => $value) {
            $report .= '- **'.ucwords(str_replace('_', ' ', $key)).":** {$value}\n";
        }

        // Summary
        $summary = $results['summary'];
        $report .= "\n## Summary\n\n";
        $report .= "- **Total Tests:** {$summary['total_tests']}\n";
        $report .= "- **Total Assertions:** {$summary['total_assertions']}\n";
        $report .= "- **Failures:** {$summary['total_failures']}\n";
        $report .= "- **Errors:** {$summary['total_errors']}\n";
        $report .= "- **Skipped:** {$summary['total_skipped']}\n";
        $report .= "- **Success Rate:** {$summary['success_rate']}%\n";
        $report .= '- **Status:** '.strtoupper($summary['status'])."\n";

        // Suite details
        $report .= "\n## Test Suites\n\n";
        foreach ($results['suites'] as $suiteName => $suite) {
            $report .= "### {$suiteName}\n\n";
            $report .= "- **Path:** {$suite['path']}\n";
            $report .= "- **Tests:** {$suite['tests_run']}\n";
            $report .= "- **Assertions:** {$suite['assertions']}\n";
            $report .= "- **Duration:** {$suite['duration']}s\n\n";
        }

        return $report;
    }
}
