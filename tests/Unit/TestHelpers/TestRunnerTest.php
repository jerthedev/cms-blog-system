<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Unit\TestHelpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JTD\CMSBlogSystem\Tests\TestCase;
use JTD\CMSBlogSystem\Tests\TestHelpers\TestRunner;

/**
 * Test Runner Test
 *
 * Tests the TestRunner utility class.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class TestRunnerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_test_environment(): void
    {
        $environment = TestRunner::getTestEnvironment();

        $this->assertIsArray($environment);
        $this->assertArrayHasKey('php_version', $environment);
        $this->assertArrayHasKey('laravel_version', $environment);
        $this->assertArrayHasKey('database_driver', $environment);
        $this->assertArrayHasKey('database_name', $environment);
        $this->assertArrayHasKey('memory_limit', $environment);
        $this->assertArrayHasKey('time_limit', $environment);
        $this->assertArrayHasKey('environment', $environment);
        $this->assertArrayHasKey('debug_mode', $environment);
        $this->assertArrayHasKey('cache_driver', $environment);
        $this->assertArrayHasKey('queue_driver', $environment);

        // Check that values are not empty
        $this->assertNotEmpty($environment['php_version']);
        $this->assertNotEmpty($environment['laravel_version']);
        $this->assertNotEmpty($environment['database_driver']);
    }

    /** @test */
    public function it_can_generate_summary(): void
    {
        $suites = [
            'Unit Tests' => [
                'tests_run' => 50,
                'assertions' => 200,
                'failures' => 2,
                'errors' => 1,
                'skipped' => 3,
            ],
            'Feature Tests' => [
                'tests_run' => 30,
                'assertions' => 150,
                'failures' => 0,
                'errors' => 0,
                'skipped' => 1,
            ],
        ];

        $summary = TestRunner::generateSummary($suites);

        $this->assertIsArray($summary);
        $this->assertEquals(80, $summary['total_tests']);
        $this->assertEquals(350, $summary['total_assertions']);
        $this->assertEquals(2, $summary['total_failures']);
        $this->assertEquals(1, $summary['total_errors']);
        $this->assertEquals(4, $summary['total_skipped']);
        $this->assertEquals(96.25, $summary['success_rate']); // 77 successful out of 80
        $this->assertEquals('error', $summary['status']); // Has errors, so status is error
    }

    /** @test */
    public function it_can_run_database_integrity_checks(): void
    {
        $checks = TestRunner::runDatabaseIntegrityChecks();

        $this->assertIsArray($checks);
        $this->assertArrayHasKey('tables_exist', $checks);
        $this->assertArrayHasKey('foreign_keys', $checks);
        $this->assertArrayHasKey('indexes', $checks);
        $this->assertArrayHasKey('constraints', $checks);
        $this->assertArrayHasKey('overall_status', $checks);

        // Each check should have status and message
        foreach (['tables_exist', 'foreign_keys', 'indexes', 'constraints'] as $checkName) {
            $check = $checks[$checkName];
            $this->assertArrayHasKey('status', $check);
            $this->assertArrayHasKey('message', $check);
            $this->assertContains($check['status'], ['pass', 'fail']);
        }
    }

    /** @test */
    public function it_can_check_tables_exist(): void
    {
        $checks = TestRunner::runDatabaseIntegrityChecks();
        $tableCheck = $checks['tables_exist'];

        $this->assertEquals('pass', $tableCheck['status']);
        $this->assertStringContainsString('All required tables exist', $tableCheck['message']);
        $this->assertArrayHasKey('details', $tableCheck);
        $this->assertArrayHasKey('required', $tableCheck['details']);
        $this->assertArrayHasKey('missing', $tableCheck['details']);
        $this->assertEmpty($tableCheck['details']['missing']);
    }

    /** @test */
    public function it_handles_sqlite_foreign_keys_gracefully(): void
    {
        $checks = TestRunner::runDatabaseIntegrityChecks();
        $foreignKeyCheck = $checks['foreign_keys'];

        // Should pass for SQLite (we skip detailed checking)
        $this->assertEquals('pass', $foreignKeyCheck['status']);
        $this->assertStringContainsString('SQLite', $foreignKeyCheck['message']);
    }

    /** @test */
    public function it_can_check_indexes(): void
    {
        $checks = TestRunner::runDatabaseIntegrityChecks();
        $indexCheck = $checks['indexes'];

        $this->assertEquals('pass', $indexCheck['status']);
        $this->assertStringContainsString('All expected indexes exist', $indexCheck['message']);
    }

    /** @test */
    public function it_can_check_constraints(): void
    {
        $checks = TestRunner::runDatabaseIntegrityChecks();
        $constraintCheck = $checks['constraints'];

        $this->assertEquals('pass', $constraintCheck['status']);
        $this->assertStringContainsString('All constraints are valid', $constraintCheck['message']);
    }

    /** @test */
    public function it_can_generate_report(): void
    {
        $results = [
            'start_time' => now()->subMinutes(5),
            'end_time' => now(),
            'duration' => 300,
            'environment' => [
                'php_version' => '8.2.0',
                'laravel_version' => '10.0.0',
                'database_driver' => 'sqlite',
            ],
            'suites' => [
                'Unit Tests' => [
                    'path' => 'tests/Unit',
                    'tests_run' => 50,
                    'assertions' => 200,
                    'duration' => 2.5,
                ],
            ],
            'summary' => [
                'total_tests' => 50,
                'total_assertions' => 200,
                'total_failures' => 0,
                'total_errors' => 0,
                'total_skipped' => 1,
                'success_rate' => 100.0,
                'status' => 'success',
            ],
        ];

        $report = TestRunner::generateReport($results);

        $this->assertIsString($report);
        $this->assertStringContainsString('# Blog System Test Report', $report);
        $this->assertStringContainsString('## Environment', $report);
        $this->assertStringContainsString('## Summary', $report);
        $this->assertStringContainsString('## Test Suites', $report);
        $this->assertStringContainsString('**Total Tests:** 50', $report);
        $this->assertStringContainsString('**Success Rate:** 100%', $report);
        $this->assertStringContainsString('**Status:** SUCCESS', $report);
    }

    /** @test */
    public function it_determines_status_correctly(): void
    {
        // Test success status
        $successSuite = [
            'tests_run' => 100,
            'assertions' => 300,
            'failures' => 0,
            'errors' => 0,
            'skipped' => 0,
        ];
        $summary = TestRunner::generateSummary(['test' => $successSuite]);
        $this->assertEquals('success', $summary['status']);

        // Test warning status (90-99% success)
        $warningSuite = [
            'tests_run' => 100,
            'assertions' => 300,
            'failures' => 5,
            'errors' => 0,
            'skipped' => 0,
        ];
        $summary = TestRunner::generateSummary(['test' => $warningSuite]);
        $this->assertEquals('warning', $summary['status']);

        // Test failure status (< 90% success)
        $failureSuite = [
            'tests_run' => 100,
            'assertions' => 300,
            'failures' => 15,
            'errors' => 0,
            'skipped' => 0,
        ];
        $summary = TestRunner::generateSummary(['test' => $failureSuite]);
        $this->assertEquals('failure', $summary['status']);

        // Test error status (has errors)
        $errorSuite = [
            'tests_run' => 100,
            'assertions' => 300,
            'failures' => 0,
            'errors' => 1,
            'skipped' => 0,
        ];
        $summary = TestRunner::generateSummary(['test' => $errorSuite]);
        $this->assertEquals('error', $summary['status']);
    }

    /** @test */
    public function it_handles_empty_suites(): void
    {
        $summary = TestRunner::generateSummary([]);

        $this->assertEquals(0, $summary['total_tests']);
        $this->assertEquals(0, $summary['total_assertions']);
        $this->assertEquals(0, $summary['total_failures']);
        $this->assertEquals(0, $summary['total_errors']);
        $this->assertEquals(0, $summary['total_skipped']);
        $this->assertEquals(0, $summary['success_rate']);
        $this->assertEquals('unknown', $summary['status']);
    }

    /** @test */
    public function it_can_run_test_suite(): void
    {
        $result = TestRunner::runTestSuite('tests/Unit');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('start_time', $result);
        $this->assertArrayHasKey('end_time', $result);
        $this->assertArrayHasKey('duration', $result);
        $this->assertArrayHasKey('tests_run', $result);
        $this->assertArrayHasKey('assertions', $result);
        $this->assertArrayHasKey('failures', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('skipped', $result);

        $this->assertEquals('tests/Unit', $result['path']);
        $this->assertIsFloat($result['duration']);
        $this->assertGreaterThanOrEqual(0, $result['duration']);
    }

    /** @test */
    public function environment_contains_expected_values(): void
    {
        $environment = TestRunner::getTestEnvironment();

        // Check PHP version format
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', $environment['php_version']);

        // Check database driver is valid
        $this->assertContains($environment['database_driver'], ['sqlite', 'mysql', 'pgsql']);

        // Check environment is testing
        $this->assertEquals('testing', $environment['environment']);
    }
}
