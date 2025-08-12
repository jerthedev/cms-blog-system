<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * CMS Blog System Install Command
 *
 * Handles the installation of the CMS Blog System package.
 * Publishes configuration, runs migrations, and sets up the package.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:install
                            {--framework= : Choose CSS framework (bootstrap|tailwind)}
                            {--force : Force overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the CMS Blog System package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing CMS Blog System...');

        // Publish configuration
        $this->publishConfiguration();

        // Publish views and assets
        $this->publishAssets();

        // Run migrations
        $this->runMigrations();

        // Set framework choice
        $this->setFramework();

        // Register with AdminPanel if available
        $this->registerWithAdminPanel();

        $this->info('✅ CMS Blog System installed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->info('1. Configure your blog settings in config/cms-blog-system.php');
        $this->info('2. Create your first blog post using the AdminPanel');
        $this->info('3. Visit /blog to see your blog in action');

        return self::SUCCESS;
    }

    /**
     * Publish configuration files.
     */
    protected function publishConfiguration(): void
    {
        $this->info('Publishing configuration...');

        $params = ['--tag' => 'cms-blog-system-config'];
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', $params);
        $this->info('✅ Configuration published');
    }

    /**
     * Publish views and assets.
     */
    protected function publishAssets(): void
    {
        $this->info('Publishing views and assets...');

        // Get framework choice (will be set later in setFramework method)
        $framework = $this->option('framework');

        // If framework is specified, use framework-specific publishing
        if ($framework && in_array($framework, ['bootstrap', 'tailwind'])) {
            $viewTag = "cms-blog-system-views-{$framework}";
            $this->info("Publishing {$framework} templates...");
        } else {
            // Use auto-detect publishing (will use config)
            $viewTag = 'cms-blog-system-views';
            $this->info('Publishing templates (auto-detect framework)...');
        }

        $params = ['--tag' => $viewTag];
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', $params);

        $params = ['--tag' => 'cms-blog-system-assets'];
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', $params);

        $this->info('✅ Views and assets published');
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        $this->info('Running migrations...');

        if ($this->confirm('Do you want to run the database migrations now?', true)) {
            Artisan::call('migrate');
            $this->info('✅ Migrations completed');
        } else {
            $this->warn('⚠️  Remember to run "php artisan migrate" to create the blog tables');
        }
    }

    /**
     * Set the CSS framework choice.
     */
    protected function setFramework(): void
    {
        $framework = $this->option('framework');

        // If no framework specified or invalid framework, prompt user
        if (empty($framework) || ! in_array($framework, ['bootstrap', 'tailwind'])) {
            $framework = $this->choice(
                'Which CSS framework would you like to use?',
                ['bootstrap', 'tailwind'],
                'bootstrap'
            );
        }

        // Update the configuration file
        $configPath = config_path('cms-blog-system.php');
        if (file_exists($configPath)) {
            $config = file_get_contents($configPath);
            $config = preg_replace(
                "/'framework' => env\('CMS_BLOG_FRAMEWORK', '[^']+'\)/",
                "'framework' => env('CMS_BLOG_FRAMEWORK', '{$framework}')",
                $config
            );
            file_put_contents($configPath, $config);

            $this->info("✅ Framework set to: {$framework}");
        }
    }

    /**
     * Register with AdminPanel if available.
     */
    protected function registerWithAdminPanel(): void
    {
        if (class_exists(\JTD\AdminPanel\Support\AdminPanel::class)) {
            $this->info('AdminPanel detected - Blog resources will be automatically registered');
            $this->info('✅ AdminPanel integration enabled');
        } else {
            $this->warn('AdminPanel not detected - Install jerthedev/admin-panel for admin interface');
        }
    }
}
