<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JTD\CMSBlogSystem\Models\BlogCategory;

/**
 * BlogCategory Factory
 *
 * Factory for generating test BlogCategory instances.
 *
 * @extends Factory<BlogCategory>
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = BlogCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => ucwords($name),
            'description' => $this->faker->optional(0.8)->paragraph(2),
            'parent_id' => null, // Default to root category
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => true, // Default to active
            'meta_title' => $this->faker->optional(0.6)->sentence(6),
            'meta_description' => $this->faker->optional(0.7)->paragraph(1),
        ];
    }

    /**
     * Create a root category (no parent).
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create a child category with a specific parent.
     */
    public function childOf(BlogCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Create an active category.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive category.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a category with SEO fields.
     */
    public function withSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_title' => $this->faker->sentence(8),
            'meta_description' => $this->faker->paragraph(1),
        ]);
    }

    /**
     * Create a category with a specific sort order.
     */
    public function withSortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }

    /**
     * Create a category with a specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Create a technology-related category.
     */
    public function technology(): static
    {
        $techCategories = [
            'Programming', 'Web Development', 'Mobile Development', 'DevOps',
            'Data Science', 'Machine Learning', 'Cybersecurity', 'Cloud Computing',
            'Frontend', 'Backend', 'Full Stack', 'JavaScript', 'PHP', 'Python',
            'React', 'Vue.js', 'Laravel', 'Node.js', 'Docker', 'Kubernetes',
        ];

        $name = $this->faker->randomElement($techCategories);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'description' => "Articles and tutorials about {$name} development and best practices.",
        ]);
    }

    /**
     * Create a business-related category.
     */
    public function business(): static
    {
        $businessCategories = [
            'Marketing', 'Sales', 'Finance', 'Management', 'Strategy',
            'Entrepreneurship', 'Leadership', 'Productivity', 'Innovation',
            'Digital Marketing', 'Content Marketing', 'SEO', 'Social Media',
        ];

        $name = $this->faker->randomElement($businessCategories);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'description' => "Business insights and strategies related to {$name}.",
        ]);
    }

    /**
     * Create a lifestyle-related category.
     */
    public function lifestyle(): static
    {
        $lifestyleCategories = [
            'Health & Wellness', 'Travel', 'Food & Cooking', 'Fitness',
            'Personal Development', 'Hobbies', 'Fashion', 'Home & Garden',
            'Relationships', 'Parenting', 'Education', 'Books & Reading',
        ];

        $name = $this->faker->randomElement($lifestyleCategories);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'description' => "Lifestyle content focused on {$name} and related topics.",
        ]);
    }

    /**
     * Create a hierarchical structure with parent and children.
     */
    public function withChildren(int $childrenCount = 3): static
    {
        return $this->afterCreating(function (BlogCategory $category) use ($childrenCount) {
            BlogCategory::factory()
                ->count($childrenCount)
                ->childOf($category)
                ->create();
        });
    }

    /**
     * Create a deep hierarchy (3 levels).
     */
    public function deepHierarchy(): static
    {
        return $this->afterCreating(function (BlogCategory $grandparent) {
            // Create parent categories
            $parents = BlogCategory::factory()
                ->count(2)
                ->childOf($grandparent)
                ->create();

            // Create children for each parent
            foreach ($parents as $parent) {
                BlogCategory::factory()
                    ->count(3)
                    ->childOf($parent)
                    ->create();
            }
        });
    }
}
