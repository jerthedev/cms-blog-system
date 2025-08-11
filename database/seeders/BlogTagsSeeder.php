<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Seeders;

use Illuminate\Database\Seeder;
use JTD\CMSBlogSystem\Models\BlogTag;

/**
 * Blog Tags Seeder
 *
 * Seeds default blog tags for common topics.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = $this->getDefaultTags();

        foreach ($tags as $tagData) {
            $this->createTag($tagData);
        }

        $this->command->info('✅ Created '.BlogTag::count().' blog tags');
    }

    /**
     * Get the default tags to seed.
     */
    protected function getDefaultTags(): array
    {
        return [
            // Programming Languages
            [
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'PHP programming language posts and tutorials.',
                'color' => '#777BB4',
            ],
            [
                'name' => 'JavaScript',
                'slug' => 'javascript',
                'description' => 'JavaScript programming and modern frameworks.',
                'color' => '#F7DF1E',
            ],
            [
                'name' => 'Python',
                'slug' => 'python',
                'description' => 'Python programming language and frameworks.',
                'color' => '#3776AB',
            ],
            [
                'name' => 'TypeScript',
                'slug' => 'typescript',
                'description' => 'TypeScript programming and type-safe development.',
                'color' => '#3178C6',
            ],

            // Frameworks & Libraries
            [
                'name' => 'Laravel',
                'slug' => 'laravel',
                'description' => 'Laravel PHP framework tutorials and best practices.',
                'color' => '#FF2D20',
            ],
            [
                'name' => 'Vue.js',
                'slug' => 'vuejs',
                'description' => 'Vue.js framework and progressive web apps.',
                'color' => '#4FC08D',
            ],
            [
                'name' => 'React',
                'slug' => 'react',
                'description' => 'React library and component-based development.',
                'color' => '#61DAFB',
            ],
            [
                'name' => 'Node.js',
                'slug' => 'nodejs',
                'description' => 'Node.js server-side JavaScript development.',
                'color' => '#339933',
            ],
            [
                'name' => 'Framework',
                'slug' => 'framework',
                'description' => 'General framework development and architecture.',
                'color' => '#6C757D',
            ],

            // Development Topics
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'General web development topics and trends.',
                'color' => '#007ACC',
            ],
            [
                'name' => 'API Development',
                'slug' => 'api-development',
                'description' => 'REST API, GraphQL, and web service development.',
                'color' => '#FF6B35',
            ],
            [
                'name' => 'Database',
                'slug' => 'database',
                'description' => 'Database design, optimization, and management.',
                'color' => '#336791',
            ],
            [
                'name' => 'DevOps',
                'slug' => 'devops',
                'description' => 'DevOps practices, CI/CD, and deployment strategies.',
                'color' => '#326CE5',
            ],

            // Content Types
            [
                'name' => 'Tutorial',
                'slug' => 'tutorial',
                'description' => 'Step-by-step tutorials and how-to guides.',
                'color' => '#28A745',
            ],
            [
                'name' => 'Tips',
                'slug' => 'tips',
                'description' => 'Quick tips and tricks for developers.',
                'color' => '#FFC107',
            ],
            [
                'name' => 'Best Practices',
                'slug' => 'best-practices',
                'description' => 'Industry best practices and coding standards.',
                'color' => '#17A2B8',
            ],
            [
                'name' => 'Case Study',
                'slug' => 'case-study',
                'description' => 'Real-world case studies and project examples.',
                'color' => '#6F42C1',
            ],

            // Skill Levels
            [
                'name' => 'Beginner',
                'slug' => 'beginner',
                'description' => 'Content suitable for beginners and newcomers.',
                'color' => '#28A745',
            ],
            [
                'name' => 'Intermediate',
                'slug' => 'intermediate',
                'description' => 'Content for developers with some experience.',
                'color' => '#FFC107',
            ],
            [
                'name' => 'Advanced',
                'slug' => 'advanced',
                'description' => 'Advanced content for experienced developers.',
                'color' => '#DC3545',
            ],

            // Tools & Technologies
            [
                'name' => 'Git',
                'slug' => 'git',
                'description' => 'Version control with Git and GitHub.',
                'color' => '#F05032',
            ],
            [
                'name' => 'Docker',
                'slug' => 'docker',
                'description' => 'Containerization with Docker and orchestration.',
                'color' => '#2496ED',
            ],
            [
                'name' => 'Testing',
                'slug' => 'testing',
                'description' => 'Software testing, TDD, and quality assurance.',
                'color' => '#6C757D',
            ],
            [
                'name' => 'Performance',
                'slug' => 'performance',
                'description' => 'Performance optimization and monitoring.',
                'color' => '#FF6B35',
            ],

            // General Topics
            [
                'name' => 'Open Source',
                'slug' => 'open-source',
                'description' => 'Open source projects and contributions.',
                'color' => '#000000',
            ],
            [
                'name' => 'Career',
                'slug' => 'career',
                'description' => 'Career advice and professional development.',
                'color' => '#6F42C1',
            ],
            [
                'name' => 'Industry',
                'slug' => 'industry',
                'description' => 'Technology industry trends and insights.',
                'color' => '#495057',
            ],
        ];
    }

    /**
     * Create a tag if it doesn't already exist.
     */
    protected function createTag(array $tagData): BlogTag
    {
        // Check if tag already exists
        $existing = BlogTag::where('slug', $tagData['slug'])->first();

        if ($existing) {
            $this->command->info("⏭️ Tag '{$tagData['name']}' already exists, skipping...");

            return $existing;
        }

        // Create the tag
        $tag = BlogTag::create([
            'name' => $tagData['name'],
            'slug' => $tagData['slug'],
            'description' => $tagData['description'] ?? null,
            'color' => $tagData['color'] ?? null,
            'usage_count' => 0, // Will be updated when posts are attached
        ]);

        $this->command->info("✅ Created tag: {$tag->name}");

        return $tag;
    }
}
