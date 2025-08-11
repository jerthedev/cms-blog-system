<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JTD\CMSBlogSystem\Models\BlogTag;

/**
 * BlogTag Factory
 *
 * Factory for generating test BlogTag instances.
 *
 * @extends Factory<BlogTag>
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogTagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = BlogTag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'name' => ucfirst($name),
            'description' => $this->faker->optional(0.7)->sentence(),
            'color' => $this->faker->optional(0.8)->hexColor(),
            'usage_count' => $this->faker->numberBetween(0, 50),
            'is_active' => true, // Default to active
        ];
    }

    /**
     * Create an active tag.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive tag.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a popular tag with high usage count.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => $this->faker->numberBetween(20, 100),
        ]);
    }

    /**
     * Create an unused tag (usage count = 0).
     */
    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => 0,
        ]);
    }

    /**
     * Create a tag with specific usage count.
     */
    public function withUsageCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => $count,
        ]);
    }

    /**
     * Create a tag with a specific color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    /**
     * Create a tag with a specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Create a technology-related tag.
     */
    public function technology(): static
    {
        $techTags = [
            'PHP', 'JavaScript', 'Python', 'Java', 'C++', 'React', 'Vue.js', 'Angular',
            'Laravel', 'Symfony', 'Django', 'Spring', 'Node.js', 'Express', 'FastAPI',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'MySQL', 'PostgreSQL',
            'MongoDB', 'Redis', 'Git', 'GitHub', 'GitLab', 'CI/CD', 'DevOps',
            'Machine Learning', 'AI', 'Data Science', 'Big Data', 'Blockchain',
            'Microservices', 'API', 'REST', 'GraphQL', 'WebSocket', 'OAuth',
            'JWT', 'SSL', 'HTTPS', 'Security', 'Testing', 'TDD', 'BDD',
        ];

        $name = $this->faker->randomElement($techTags);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'description' => "Everything related to {$name} development and best practices.",
            'color' => $this->getTechColor($name),
        ]);
    }

    /**
     * Create a business-related tag.
     */
    public function business(): static
    {
        $businessTags = [
            'Marketing', 'Sales', 'Finance', 'Management', 'Strategy', 'Leadership',
            'Entrepreneurship', 'Startup', 'Innovation', 'Productivity', 'Growth',
            'Digital Marketing', 'Content Marketing', 'SEO', 'SEM', 'Social Media',
            'Email Marketing', 'Affiliate Marketing', 'Branding', 'Customer Service',
            'Project Management', 'Agile', 'Scrum', 'Remote Work', 'Team Building',
            'Negotiation', 'Networking', 'Investment', 'Funding', 'IPO', 'M&A',
        ];

        $name = $this->faker->randomElement($businessTags);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'description' => "Business insights and strategies related to {$name}.",
            'color' => $this->getBusinessColor($name),
        ]);
    }

    /**
     * Create a lifestyle-related tag.
     */
    public function lifestyle(): static
    {
        $lifestyleTags = [
            'Health', 'Fitness', 'Wellness', 'Nutrition', 'Diet', 'Exercise',
            'Travel', 'Adventure', 'Food', 'Cooking', 'Recipe', 'Restaurant',
            'Fashion', 'Style', 'Beauty', 'Skincare', 'Makeup', 'Hair',
            'Home', 'Garden', 'DIY', 'Decoration', 'Interior Design',
            'Relationships', 'Family', 'Parenting', 'Education', 'Learning',
            'Books', 'Reading', 'Movies', 'Music', 'Art', 'Photography',
            'Hobbies', 'Sports', 'Gaming', 'Entertainment', 'Culture',
        ];

        $name = $this->faker->randomElement($lifestyleTags);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'description' => "Lifestyle content focused on {$name} and related topics.",
            'color' => $this->getLifestyleColor($name),
        ]);
    }

    /**
     * Create a trending tag (recently popular).
     */
    public function trending(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => $this->faker->numberBetween(15, 40),
            'updated_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a tag with description.
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->sentence(10),
        ]);
    }

    /**
     * Get technology-related color.
     */
    protected function getTechColor(string $name): string
    {
        $techColors = [
            'PHP' => '#777BB4',
            'JavaScript' => '#F7DF1E',
            'Python' => '#3776AB',
            'Java' => '#ED8B00',
            'React' => '#61DAFB',
            'Vue.js' => '#4FC08D',
            'Laravel' => '#FF2D20',
            'Docker' => '#2496ED',
            'AWS' => '#FF9900',
        ];

        return $techColors[$name] ?? $this->faker->hexColor();
    }

    /**
     * Get business-related color.
     */
    protected function getBusinessColor(string $name): string
    {
        $businessColors = [
            '#1E88E5', '#43A047', '#FB8C00', '#8E24AA',
            '#D81B60', '#00ACC1', '#FFB300', '#546E7A',
        ];

        return $this->faker->randomElement($businessColors);
    }

    /**
     * Get lifestyle-related color.
     */
    protected function getLifestyleColor(string $name): string
    {
        $lifestyleColors = [
            '#E91E63', '#9C27B0', '#673AB7', '#3F51B5',
            '#2196F3', '#03DAC6', '#4CAF50', '#8BC34A',
            '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800',
        ];

        return $this->faker->randomElement($lifestyleColors);
    }
}
