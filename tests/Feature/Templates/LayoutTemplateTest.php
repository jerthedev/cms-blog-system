<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Tests\Feature\Templates;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use JTD\CMSBlogSystem\Tests\TestCase;

/**
 * Layout Template Test
 *
 * Tests the foundational layout template system including single, two-column,
 * and three-column layouts with Bootstrap and Tailwind variants.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class LayoutTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set default framework for testing
        config(['cms-blog-system.framework' => 'bootstrap']);
    }

    /** @test */
    public function it_can_render_single_column_layout_with_bootstrap(): void
    {
        Config::set('cms-blog-system.framework', 'bootstrap');

        $content = 'Test single column content';

        $view = View::make('cms-blog-system::layouts.single-column', [
            'content' => $content,
            'title' => 'Single Column Test',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($content, $rendered);
        $this->assertStringContainsString('container', $rendered);
        $this->assertStringContainsString('col-12', $rendered);
        $this->assertStringNotContainsString('sidebar', $rendered);
    }

    /** @test */
    public function it_can_render_single_column_layout_with_tailwind(): void
    {
        Config::set('cms-blog-system.framework', 'tailwind');

        $content = 'Test single column content';

        $view = View::make('cms-blog-system::layouts.single-column', [
            'content' => $content,
            'title' => 'Single Column Test',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($content, $rendered);
        $this->assertStringContainsString('max-w-7xl', $rendered);
        $this->assertStringContainsString('mx-auto', $rendered);
        $this->assertStringNotContainsString('sidebar', $rendered);
    }

    /** @test */
    public function it_can_render_two_column_layout_with_bootstrap(): void
    {
        Config::set('cms-blog-system.framework', 'bootstrap');

        $mainContent = 'Main content area';
        $sidebarContent = 'Sidebar content';

        $view = View::make('cms-blog-system::layouts.two-column', [
            'mainContent' => $mainContent,
            'sidebarContent' => $sidebarContent,
            'title' => 'Two Column Test',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($mainContent, $rendered);
        $this->assertStringContainsString($sidebarContent, $rendered);
        $this->assertStringContainsString('col-md-8', $rendered);
        $this->assertStringContainsString('col-md-4', $rendered);
        $this->assertStringContainsString('row', $rendered);
    }

    /** @test */
    public function it_can_render_two_column_layout_with_tailwind(): void
    {
        Config::set('cms-blog-system.framework', 'tailwind');

        $mainContent = 'Main content area';
        $sidebarContent = 'Sidebar content';

        $view = View::make('cms-blog-system::layouts.two-column', [
            'mainContent' => $mainContent,
            'sidebarContent' => $sidebarContent,
            'title' => 'Two Column Test',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($mainContent, $rendered);
        $this->assertStringContainsString($sidebarContent, $rendered);
        $this->assertStringContainsString('lg:w-2/3', $rendered);
        $this->assertStringContainsString('lg:w-1/3', $rendered);
        $this->assertStringContainsString('flex', $rendered);
    }

    /** @test */
    public function it_can_render_three_column_layout_with_bootstrap(): void
    {
        Config::set('cms-blog-system.framework', 'bootstrap');

        $mainContent = 'Main content area';
        $leftSidebar = 'Left sidebar content';
        $rightSidebar = 'Right sidebar content';

        $view = View::make('cms-blog-system::layouts.three-column', [
            'mainContent' => $mainContent,
            'leftSidebar' => $leftSidebar,
            'rightSidebar' => $rightSidebar,
            'title' => 'Three Column Test',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($mainContent, $rendered);
        $this->assertStringContainsString($leftSidebar, $rendered);
        $this->assertStringContainsString($rightSidebar, $rendered);
        $this->assertStringContainsString('col-md-6', $rendered);
        $this->assertStringContainsString('col-md-3', $rendered);
    }

    /** @test */
    public function it_can_render_three_column_layout_with_tailwind(): void
    {
        Config::set('cms-blog-system.framework', 'tailwind');

        $mainContent = 'Main content area';
        $leftSidebar = 'Left sidebar content';
        $rightSidebar = 'Right sidebar content';

        $view = View::make('cms-blog-system::layouts.three-column', [
            'mainContent' => $mainContent,
            'leftSidebar' => $leftSidebar,
            'rightSidebar' => $rightSidebar,
            'title' => 'Three Column Test',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString($mainContent, $rendered);
        $this->assertStringContainsString($leftSidebar, $rendered);
        $this->assertStringContainsString($rightSidebar, $rendered);
        $this->assertStringContainsString('lg:w-1/2', $rendered);
        $this->assertStringContainsString('lg:w-1/4', $rendered);
    }

    /** @test */
    public function it_extends_host_project_layout(): void
    {
        $view = View::make('cms-blog-system::layouts.single-column', [
            'content' => 'Test content',
            'title' => 'Test Title',
        ]);

        $rendered = $view->render();

        // Should extend the main app layout
        $this->assertStringContainsString('<!DOCTYPE html', $rendered);
        $this->assertStringContainsString('<html lang=', $rendered);
        $this->assertStringContainsString('<head>', $rendered);
        $this->assertStringContainsString('<body', $rendered);
    }

    /** @test */
    public function it_includes_responsive_design_classes(): void
    {
        Config::set('cms-blog-system.framework', 'bootstrap');

        $view = View::make('cms-blog-system::layouts.two-column', [
            'mainContent' => 'Main content',
            'sidebarContent' => 'Sidebar content',
        ]);

        $rendered = $view->render();

        // Bootstrap responsive classes
        $this->assertStringContainsString('col-12', $rendered);
        $this->assertStringContainsString('col-md-', $rendered);

        Config::set('cms-blog-system.framework', 'tailwind');

        $view = View::make('cms-blog-system::layouts.two-column', [
            'mainContent' => 'Main content',
            'sidebarContent' => 'Sidebar content',
        ]);

        $rendered = $view->render();

        // Tailwind responsive classes
        $this->assertStringContainsString('lg:', $rendered);
        $this->assertStringContainsString('sm:', $rendered);
    }

    /** @test */
    public function it_includes_accessibility_attributes(): void
    {
        $view = View::make('cms-blog-system::layouts.two-column', [
            'mainContent' => 'Main content',
            'sidebarContent' => 'Sidebar content',
        ]);

        $rendered = $view->render();

        // Should include semantic HTML and ARIA attributes
        $this->assertStringContainsString('role="main"', $rendered);
        $this->assertStringContainsString('role="complementary"', $rendered);
        $this->assertStringContainsString('<main', $rendered);
        $this->assertStringContainsString('<aside', $rendered);
    }

    /** @test */
    public function it_can_customize_column_widths(): void
    {
        Config::set('cms-blog-system.framework', 'bootstrap');

        $view = View::make('cms-blog-system::layouts.two-column', [
            'mainContent' => 'Main content',
            'sidebarContent' => 'Sidebar content',
            'mainWidth' => 9,
            'sidebarWidth' => 3,
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('col-md-9', $rendered);
        $this->assertStringContainsString('col-md-3', $rendered);
    }

    /** @test */
    public function it_handles_missing_content_gracefully(): void
    {
        $view = View::make('cms-blog-system::layouts.single-column', [
            'title' => 'Test Title',
        ]);

        $rendered = $view->render();

        // Should render without errors even with missing content
        $this->assertStringContainsString('Test Title', $rendered);
        $this->assertStringContainsString('<!DOCTYPE html', $rendered);
    }

    /** @test */
    public function it_supports_custom_css_classes(): void
    {
        $view = View::make('cms-blog-system::layouts.single-column', [
            'content' => 'Test content',
            'containerClass' => 'custom-container',
            'contentClass' => 'custom-content',
        ]);

        $rendered = $view->render();

        $this->assertStringContainsString('custom-container', $rendered);
        $this->assertStringContainsString('custom-content', $rendered);
    }

    /** @test */
    public function it_can_be_published_and_customized(): void
    {
        // Test that Bootstrap templates can be published to host project
        $this->artisan('vendor:publish', [
            '--tag' => 'cms-blog-system-views-bootstrap',
            '--force' => true,
        ]);

        // Check that published views exist in standard Laravel paths
        $layoutsPath = resource_path('views/layouts');
        $partialsPath = resource_path('views/partials');

        $this->assertDirectoryExists($layoutsPath);
        $this->assertDirectoryExists($partialsPath);

        // Check specific layout files exist
        $this->assertFileExists($layoutsPath.'/single-column.blade.php');
        $this->assertFileExists($layoutsPath.'/two-column.blade.php');
        $this->assertFileExists($layoutsPath.'/three-column.blade.php');
        $this->assertFileExists($partialsPath.'/alerts.blade.php');

        // Test that Tailwind templates can also be published
        $this->artisan('vendor:publish', [
            '--tag' => 'cms-blog-system-views-tailwind',
            '--force' => true,
        ]);

        // Files should still exist (overwritten with Tailwind versions)
        $this->assertFileExists($layoutsPath.'/single-column.blade.php');
        $this->assertFileExists($layoutsPath.'/two-column.blade.php');
        $this->assertFileExists($layoutsPath.'/three-column.blade.php');
        $this->assertFileExists($partialsPath.'/alerts.blade.php');
    }
}
