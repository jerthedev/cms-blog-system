<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Blog Seeder
 *
 * Main seeder for the blog system that runs all blog-related seeders.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding blog system data...');

        // Seed categories first (needed for posts)
        $this->command->info('📁 Seeding blog categories...');
        $this->call(BlogCategoriesSeeder::class);

        // Seed tags (needed for posts)
        $this->command->info('🏷️ Seeding blog tags...');
        $this->call(BlogTagsSeeder::class);

        // Seed posts with relationships
        $this->command->info('📝 Seeding blog posts...');
        $this->call(BlogPostsSeeder::class);

        $this->command->info('✅ Blog system seeding completed!');
    }
}
