<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Setup Media Library Command
 *
 * Helps set up Spatie Media Library for the blog system.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class SetupMediaLibraryCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cms-blog:setup-media
                            {--force : Force setup even if already configured}
                            {--check : Only check if media library is configured}';

    /**
     * The console command description.
     */
    protected $description = 'Set up Spatie Media Library for the blog system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('check')) {
            return $this->checkMediaLibraryStatus();
        }

        $this->info('🔧 Setting up Spatie Media Library for CMS Blog System...');

        // Check if already configured
        if ($this->isMediaLibraryConfigured() && ! $this->option('force')) {
            $this->info('✅ Media Library is already configured!');
            $this->displayStatus();

            return self::SUCCESS;
        }

        // Check if Spatie Media Library package is installed
        if (! $this->isMediaLibraryPackageInstalled()) {
            $this->error('❌ Spatie Media Library package is not installed.');
            $this->info('💡 Install it with: composer require spatie/laravel-medialibrary');

            return self::FAILURE;
        }

        // Publish media library migrations
        $this->info('📦 Publishing Media Library migrations...');

        try {
            Artisan::call('vendor:publish', [
                '--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
                '--tag' => 'medialibrary-migrations',
                '--force' => $this->option('force'),
            ]);

            $this->info('✅ Media Library migrations published successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Failed to publish migrations: '.$e->getMessage());

            return self::FAILURE;
        }

        // Run migrations
        if ($this->confirm('🚀 Run migrations now?', true)) {
            $this->info('🔄 Running migrations...');

            try {
                Artisan::call('migrate', ['--force' => true]);
                $this->info('✅ Migrations completed successfully!');
            } catch (\Exception $e) {
                $this->error('❌ Migration failed: '.$e->getMessage());
                $this->info('💡 You can run migrations manually with: php artisan migrate');

                return self::FAILURE;
            }
        }

        // Publish media library config (optional)
        if ($this->confirm('📝 Publish Media Library configuration?', false)) {
            try {
                Artisan::call('vendor:publish', [
                    '--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
                    '--tag' => 'medialibrary-config',
                    '--force' => $this->option('force'),
                ]);

                $this->info('✅ Media Library configuration published!');
            } catch (\Exception $e) {
                $this->warn('⚠️ Failed to publish config: '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->info('🎉 Media Library setup complete!');
        $this->displayStatus();

        return self::SUCCESS;
    }

    /**
     * Check media library status only.
     */
    protected function checkMediaLibraryStatus(): int
    {
        $this->info('🔍 Checking Media Library status...');
        $this->displayStatus();

        return $this->isMediaLibraryConfigured() ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Display current media library status.
     */
    protected function displayStatus(): void
    {
        $this->newLine();
        $this->info('📊 Media Library Status:');

        // Check package installation
        $packageInstalled = $this->isMediaLibraryPackageInstalled();
        $this->line('   📦 Package Installed: '.($packageInstalled ? '✅ Yes' : '❌ No'));

        // Check table existence
        $tableExists = Schema::hasTable('media');
        $this->line('   🗄️  Media Table: '.($tableExists ? '✅ Exists' : '❌ Missing'));

        // Check service provider
        $serviceProviderRegistered = $this->isServiceProviderRegistered();
        $this->line('   ⚙️  Service Provider: '.($serviceProviderRegistered ? '✅ Registered' : '❌ Not Registered'));

        // Overall status
        $configured = $this->isMediaLibraryConfigured();
        $this->newLine();
        $this->line('   🎯 Overall Status: '.($configured ? '✅ Configured' : '❌ Not Configured'));

        if (! $configured) {
            $this->newLine();
            $this->info('💡 To set up Media Library, run:');
            $this->line('   php artisan cms-blog:setup-media');
        }
    }

    /**
     * Check if media library package is installed.
     */
    protected function isMediaLibraryPackageInstalled(): bool
    {
        return class_exists(\Spatie\MediaLibrary\MediaLibraryServiceProvider::class);
    }

    /**
     * Check if media library service provider is registered.
     */
    protected function isServiceProviderRegistered(): bool
    {
        $providers = app()->getLoadedProviders();

        return isset($providers[\Spatie\MediaLibrary\MediaLibraryServiceProvider::class]);
    }

    /**
     * Check if media library is fully configured.
     */
    protected function isMediaLibraryConfigured(): bool
    {
        return $this->isMediaLibraryPackageInstalled() &&
               $this->isServiceProviderRegistered() &&
               Schema::hasTable('media');
    }
}
