<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JTD\CMSBlogSystem\Models\BlogPost;

/**
 * BlogPost Factory
 *
 * Factory for generating test BlogPost instances.
 *
 * @extends Factory<BlogPost>
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogPostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = BlogPost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(6, true);

        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => $this->faker->paragraph(2),
            'content' => $this->generateMarkdownContent(),
            'status' => $this->faker->randomElement(['draft', 'published', 'scheduled', 'archived']),
            'featured_image' => $this->faker->optional(0.7)->imageUrl(1200, 800, 'blog'),
            'meta_title' => $this->faker->optional(0.6)->sentence(8),
            'meta_description' => $this->faker->optional(0.8)->paragraph(1),
            'meta_keywords' => $this->faker->optional(0.5)->words(5, true),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', '+1 month'),
            'author_id' => $this->faker->optional(0.9)->numberBetween(1, 10),
        ];
    }

    /**
     * Generate realistic markdown content.
     */
    protected function generateMarkdownContent(): string
    {
        $content = '# '.$this->faker->sentence(4)."\n\n";

        $content .= $this->faker->paragraph(3)."\n\n";

        $content .= '## '.$this->faker->sentence(3)."\n\n";

        $content .= $this->faker->paragraph(4)."\n\n";

        // Add a list
        $content .= "### Key Points:\n\n";
        for ($i = 0; $i < 3; $i++) {
            $content .= '- '.$this->faker->sentence(6)."\n";
        }
        $content .= "\n";

        $content .= $this->faker->paragraph(3)."\n\n";

        // Add a code block occasionally
        if ($this->faker->boolean(30)) {
            $content .= "```php\n";
            $content .= "<?php\n\n";
            $content .= "class Example {\n";
            $content .= "    public function demo() {\n";
            $content .= "        return 'Hello World!';\n";
            $content .= "    }\n";
            $content .= "}\n";
            $content .= "```\n\n";
        }

        $content .= $this->faker->paragraph(2);

        return $content;
    }

    /**
     * Create a published post.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Create a draft post.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Create a scheduled post.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'published_at' => $this->faker->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }

    /**
     * Create an archived post.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'published_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
        ]);
    }

    /**
     * Create a post with SEO fields.
     */
    public function withSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_title' => $this->faker->sentence(8),
            'meta_description' => $this->faker->paragraph(1),
            'meta_keywords' => implode(', ', $this->faker->words(6)),
        ]);
    }

    /**
     * Create a post with featured image.
     */
    public function withFeaturedImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured_image' => $this->faker->imageUrl(1200, 800, 'blog', true),
        ]);
    }

    /**
     * Create a post with custom excerpt.
     */
    public function withExcerpt(): static
    {
        return $this->state(fn (array $attributes) => [
            'excerpt' => $this->faker->paragraph(2),
        ]);
    }
}
