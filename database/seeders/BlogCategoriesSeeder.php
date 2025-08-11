<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Seeders;

use Illuminate\Database\Seeder;
use JTD\CMSBlogSystem\Models\BlogCategory;

/**
 * Blog Categories Seeder
 *
 * Seeds default blog categories with hierarchical structure.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = $this->getDefaultCategories();

        foreach ($categories as $categoryData) {
            $this->createCategoryWithChildren($categoryData);
        }

        $this->command->info('✅ Created '.BlogCategory::count().' blog categories');
    }

    /**
     * Get the default category structure.
     */
    protected function getDefaultCategories(): array
    {
        return [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Latest technology trends, news, and insights.',
                'children' => [
                    [
                        'name' => 'Web Development',
                        'slug' => 'web-development',
                        'description' => 'Web development tutorials, frameworks, and best practices.',
                        'children' => [
                            [
                                'name' => 'Laravel',
                                'slug' => 'laravel',
                                'description' => 'Laravel framework tutorials and tips.',
                            ],
                            [
                                'name' => 'PHP',
                                'slug' => 'php',
                                'description' => 'PHP programming language guides and tutorials.',
                            ],
                            [
                                'name' => 'JavaScript',
                                'slug' => 'javascript',
                                'description' => 'JavaScript programming and modern frameworks.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Mobile Development',
                        'slug' => 'mobile-development',
                        'description' => 'Mobile app development for iOS and Android.',
                        'children' => [
                            [
                                'name' => 'iOS',
                                'slug' => 'ios',
                                'description' => 'iOS app development with Swift and Objective-C.',
                            ],
                            [
                                'name' => 'Android',
                                'slug' => 'android',
                                'description' => 'Android app development with Java and Kotlin.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'News',
                'slug' => 'news',
                'description' => 'Latest news and updates from the tech world.',
                'children' => [
                    [
                        'name' => 'Industry News',
                        'slug' => 'industry-news',
                        'description' => 'Technology industry news and announcements.',
                    ],
                    [
                        'name' => 'Product Updates',
                        'slug' => 'product-updates',
                        'description' => 'Software and product update announcements.',
                    ],
                ],
            ],
            [
                'name' => 'Tutorials',
                'slug' => 'tutorials',
                'description' => 'Step-by-step tutorials and how-to guides.',
                'children' => [
                    [
                        'name' => 'Beginner',
                        'slug' => 'beginner',
                        'description' => 'Beginner-friendly tutorials and guides.',
                    ],
                    [
                        'name' => 'Advanced',
                        'slug' => 'advanced',
                        'description' => 'Advanced tutorials for experienced developers.',
                    ],
                ],
            ],
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General blog posts and miscellaneous content.',
            ],
        ];
    }

    /**
     * Create a category with its children recursively.
     */
    protected function createCategoryWithChildren(array $categoryData, ?int $parentId = null): BlogCategory
    {
        // Check if category already exists
        $existing = BlogCategory::where('slug', $categoryData['slug'])->first();

        if ($existing) {
            $this->command->info("⏭️ Category '{$categoryData['name']}' already exists, skipping...");

            // Still process children if they exist
            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $childData) {
                    $this->createCategoryWithChildren($childData, $existing->id);
                }
            }

            return $existing;
        }

        // Create the category
        $category = BlogCategory::create([
            'name' => $categoryData['name'],
            'slug' => $categoryData['slug'],
            'description' => $categoryData['description'] ?? null,
            'parent_id' => $parentId,
            'meta_title' => $categoryData['meta_title'] ?? $categoryData['name'],
            'meta_description' => $categoryData['meta_description'] ?? $categoryData['description'] ?? null,
        ]);

        $this->command->info("✅ Created category: {$category->name}");

        // Create children if they exist
        if (isset($categoryData['children'])) {
            foreach ($categoryData['children'] as $childData) {
                $this->createCategoryWithChildren($childData, $category->id);
            }
        }

        return $category;
    }
}
