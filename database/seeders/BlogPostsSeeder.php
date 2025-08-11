<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use JTD\CMSBlogSystem\Models\BlogCategory;
use JTD\CMSBlogSystem\Models\BlogPost;
use JTD\CMSBlogSystem\Models\BlogTag;

/**
 * Blog Posts Seeder
 *
 * Seeds sample blog posts with realistic content and relationships.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = $this->getSamplePosts();

        foreach ($posts as $postData) {
            $this->createPost($postData);
        }

        $this->command->info('✅ Created '.BlogPost::count().' blog posts');
    }

    /**
     * Get sample blog posts data.
     */
    protected function getSamplePosts(): array
    {
        return [
            [
                'title' => 'Getting Started with Laravel 10: A Complete Guide',
                'slug' => 'getting-started-laravel-10-complete-guide',
                'excerpt' => 'Learn how to build modern web applications with Laravel 10. This comprehensive guide covers installation, routing, controllers, and more.',
                'content' => $this->getLaravelGuideContent(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(7),
                'categories' => ['laravel', 'web-development'],
                'tags' => ['laravel', 'php', 'tutorial', 'beginner', 'web-development'],
                'meta_title' => 'Laravel 10 Complete Guide - Build Modern Web Apps',
                'meta_description' => 'Complete guide to Laravel 10 framework. Learn routing, controllers, models, and build your first web application step by step.',
            ],
            [
                'title' => 'Modern JavaScript ES2023 Features You Should Know',
                'slug' => 'modern-javascript-es2023-features',
                'excerpt' => 'Explore the latest JavaScript features introduced in ES2023, including new array methods, improved error handling, and more.',
                'content' => BlogContentProvider::getJavaScriptFeaturesContent(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(5),
                'categories' => ['javascript', 'web-development'],
                'tags' => ['javascript', 'es2023', 'tutorial', 'intermediate', 'web-development'],
                'meta_title' => 'JavaScript ES2023 Features - Modern Development',
                'meta_description' => 'Discover the latest JavaScript ES2023 features including new array methods, error handling improvements, and modern syntax.',
            ],
            [
                'title' => 'Building RESTful APIs with PHP and Laravel',
                'slug' => 'building-restful-apis-php-laravel',
                'excerpt' => 'Learn how to design and build robust RESTful APIs using PHP and Laravel framework with proper authentication and validation.',
                'content' => BlogContentProvider::getApiDevelopmentContent(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(3),
                'categories' => ['laravel', 'php'],
                'tags' => ['laravel', 'php', 'api-development', 'tutorial', 'intermediate'],
                'meta_title' => 'RESTful APIs with Laravel - Complete Tutorial',
                'meta_description' => 'Build robust RESTful APIs with Laravel. Learn authentication, validation, error handling, and API best practices.',
            ],
            [
                'title' => 'Database Optimization Techniques for Better Performance',
                'slug' => 'database-optimization-techniques-performance',
                'excerpt' => 'Improve your application performance with these proven database optimization techniques, indexing strategies, and query optimization tips.',
                'content' => BlogContentProvider::getDatabaseOptimizationContent(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(2),
                'categories' => ['general'],
                'tags' => ['database', 'performance', 'best-practices', 'advanced', 'tips'],
                'meta_title' => 'Database Optimization - Performance Tips & Techniques',
                'meta_description' => 'Master database optimization with indexing strategies, query optimization, and performance monitoring techniques.',
            ],
            [
                'title' => 'Introduction to Test-Driven Development (TDD)',
                'slug' => 'introduction-test-driven-development-tdd',
                'excerpt' => 'Discover the benefits of Test-Driven Development and learn how to implement TDD in your projects with practical examples.',
                'content' => BlogContentProvider::getTddContent(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(1),
                'categories' => ['tutorials', 'beginner'],
                'tags' => ['testing', 'best-practices', 'tutorial', 'beginner', 'tips'],
                'meta_title' => 'Test-Driven Development Guide - TDD Best Practices',
                'meta_description' => 'Learn Test-Driven Development (TDD) with practical examples. Improve code quality and reduce bugs with TDD methodology.',
            ],
            [
                'title' => 'Docker for Developers: Containerizing Your Applications',
                'slug' => 'docker-developers-containerizing-applications',
                'excerpt' => 'Learn how to use Docker to containerize your applications, create development environments, and deploy with confidence.',
                'content' => BlogContentProvider::getDockerContent(),
                'status' => 'draft',
                'published_at' => null,
                'categories' => ['general'],
                'tags' => ['docker', 'devops', 'tutorial', 'intermediate', 'tools'],
                'meta_title' => 'Docker Tutorial - Containerize Your Applications',
                'meta_description' => 'Complete Docker guide for developers. Learn containerization, Docker Compose, and deployment strategies.',
            ],
            [
                'title' => 'Vue.js 3 Composition API: A Practical Guide',
                'slug' => 'vuejs-3-composition-api-practical-guide',
                'excerpt' => 'Master Vue.js 3 Composition API with practical examples. Learn reactive programming and component composition patterns.',
                'content' => BlogContentProvider::getVueCompositionContent(),
                'status' => 'published',
                'published_at' => Carbon::now()->subHours(12),
                'categories' => ['javascript', 'web-development'],
                'tags' => ['vuejs', 'javascript', 'tutorial', 'intermediate', 'web-development'],
                'meta_title' => 'Vue.js 3 Composition API - Complete Guide',
                'meta_description' => 'Master Vue.js 3 Composition API with practical examples, reactive programming, and modern component patterns.',
            ],
            [
                'title' => 'Career Growth Tips for Software Developers',
                'slug' => 'career-growth-tips-software-developers',
                'excerpt' => 'Practical advice for advancing your software development career, building skills, and navigating the tech industry.',
                'content' => BlogContentProvider::getCareerTipsContent(),
                'status' => 'published',
                'published_at' => Carbon::now()->subHours(6),
                'categories' => ['general'],
                'tags' => ['career', 'tips', 'industry', 'best-practices'],
                'meta_title' => 'Software Developer Career Growth - Professional Tips',
                'meta_description' => 'Advance your software development career with proven tips for skill building, networking, and professional growth.',
            ],
        ];
    }

    /**
     * Create a blog post with relationships.
     */
    protected function createPost(array $postData): BlogPost
    {
        // Check if post already exists
        $existing = BlogPost::where('slug', $postData['slug'])->first();

        if ($existing) {
            $this->command->info("⏭️ Post '{$postData['title']}' already exists, skipping...");

            return $existing;
        }

        // Create the post
        $post = BlogPost::create([
            'title' => $postData['title'],
            'slug' => $postData['slug'],
            'excerpt' => $postData['excerpt'],
            'content' => $postData['content'],
            'status' => $postData['status'],
            'published_at' => $postData['published_at'],
            'meta_title' => $postData['meta_title'] ?? null,
            'meta_description' => $postData['meta_description'] ?? null,
        ]);

        // Attach categories
        if (isset($postData['categories'])) {
            $categoryIds = [];
            foreach ($postData['categories'] as $categorySlug) {
                $category = BlogCategory::where('slug', $categorySlug)->first();
                if ($category) {
                    $categoryIds[] = $category->id;
                }
            }
            $post->categories()->attach($categoryIds);
        }

        // Attach tags
        if (isset($postData['tags'])) {
            foreach ($postData['tags'] as $tagSlug) {
                $tag = BlogTag::where('slug', $tagSlug)->first();
                if ($tag) {
                    $post->attachTag($tag->id);
                }
            }
        }

        $this->command->info("✅ Created post: {$post->title}");

        return $post;
    }

    /**
     * Get Laravel guide content.
     */
    protected function getLaravelGuideContent(): string
    {
        return <<<'MARKDOWN'
# Getting Started with Laravel 10

Laravel is a powerful PHP framework that makes web development enjoyable and creative. In this comprehensive guide, we'll walk through everything you need to know to get started with Laravel 10.

## What is Laravel?

Laravel is a web application framework with expressive, elegant syntax. It provides tools and resources to help you build robust web applications quickly and efficiently.

### Key Features

- **Eloquent ORM**: Beautiful, simple ActiveRecord implementation
- **Blade Templating**: Powerful templating engine
- **Artisan CLI**: Command-line interface for common tasks
- **Built-in Testing**: PHPUnit integration out of the box

## Installation

First, make sure you have PHP 8.1 or higher installed on your system.

```bash
# Install Laravel via Composer
composer create-project laravel/laravel my-app

# Navigate to your project
cd my-app

# Start the development server
php artisan serve
```

## Your First Route

Laravel routes are defined in the `routes/web.php` file:

```php
Route::get('/hello', function () {
    return 'Hello, Laravel!';
});
```

## Creating Controllers

Generate a controller using Artisan:

```bash
php artisan make:controller PostController
```

## Database Migrations

Create and run migrations:

```bash
# Create a migration
php artisan make:migration create_posts_table

# Run migrations
php artisan migrate
```

## Next Steps

Now that you have Laravel installed, explore these topics:

1. **Models and Eloquent ORM**
2. **Views and Blade Templates**
3. **Form Handling and Validation**
4. **Authentication and Authorization**

Laravel's documentation is excellent and covers all these topics in detail. Happy coding!
MARKDOWN;
    }
}
