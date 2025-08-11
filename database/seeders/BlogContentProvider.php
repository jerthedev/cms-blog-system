<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Database\Seeders;

/**
 * Blog Content Provider
 *
 * Provides realistic content for blog post seeders.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogContentProvider
{
    /**
     * Get JavaScript features content.
     */
    public static function getJavaScriptFeaturesContent(): string
    {
        return <<<'MARKDOWN'
# Modern JavaScript ES2023 Features

JavaScript continues to evolve with new features that make development more efficient and enjoyable. Let's explore the latest additions in ES2023.

## Array Methods

### Array.prototype.findLast()

Find the last element that matches a condition:

```javascript
const numbers = [1, 2, 3, 4, 5, 4, 3, 2, 1];
const lastEven = numbers.findLast(n => n % 2 === 0);
console.log(lastEven); // 2
```

### Array.prototype.findLastIndex()

Find the index of the last matching element:

```javascript
const lastEvenIndex = numbers.findLastIndex(n => n % 2 === 0);
console.log(lastEvenIndex); // 7
```

## Hashbang Grammar

Support for hashbang comments in JavaScript files:

```javascript
#!/usr/bin/env node
console.log('Hello from a JavaScript script!');
```

## Symbols as WeakMap Keys

WeakMaps can now use symbols as keys:

```javascript
const sym = Symbol('key');
const weakMap = new WeakMap();
weakMap.set(sym, 'value');
```

## Change Array by Copy

New methods that return modified copies instead of mutating:

```javascript
const arr = [1, 2, 3, 4, 5];

// Instead of arr.reverse()
const reversed = arr.toReversed();

// Instead of arr.sort()
const sorted = arr.toSorted((a, b) => b - a);

// Instead of arr.splice()
const spliced = arr.toSpliced(1, 2, 'new');
```

## Browser Support

These features are supported in:
- Chrome 110+
- Firefox 104+
- Safari 16+

Start using these features today to write more modern, efficient JavaScript!
MARKDOWN;
    }

    /**
     * Get API development content.
     */
    public static function getApiDevelopmentContent(): string
    {
        return <<<'MARKDOWN'
# Building RESTful APIs with Laravel

Creating robust APIs is essential for modern web applications. Laravel provides excellent tools for building RESTful APIs quickly and securely.

## API Routes

Define API routes in `routes/api.php`:

```php
Route::apiResource('posts', PostController::class);
```

This creates all standard REST endpoints:
- GET /api/posts
- POST /api/posts
- GET /api/posts/{id}
- PUT /api/posts/{id}
- DELETE /api/posts/{id}

## API Controllers

Generate an API controller:

```bash
php artisan make:controller Api/PostController --api
```

## JSON Responses

Return consistent JSON responses:

```php
public function index()
{
    $posts = Post::paginate(15);

    return response()->json([
        'data' => $posts->items(),
        'meta' => [
            'current_page' => $posts->currentPage(),
            'total' => $posts->total(),
        ]
    ]);
}
```

## Validation

Validate API requests:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
        'status' => 'in:draft,published'
    ]);

    $post = Post::create($validated);

    return response()->json($post, 201);
}
```

## Authentication

Protect your API with Sanctum:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## Error Handling

Handle API errors gracefully:

```php
public function render($request, Throwable $exception)
{
    if ($request->expectsJson()) {
        return response()->json([
            'error' => 'Something went wrong'
        ], 500);
    }

    return parent::render($request, $exception);
}
```

## Testing APIs

Test your API endpoints:

```php
public function test_can_create_post()
{
    $response = $this->postJson('/api/posts', [
        'title' => 'Test Post',
        'content' => 'Test content'
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['id', 'title', 'content']);
}
```

Building APIs with Laravel is straightforward and powerful. Follow these patterns for consistent, maintainable APIs.
MARKDOWN;
    }

    /**
     * Get database optimization content.
     */
    public static function getDatabaseOptimizationContent(): string
    {
        return <<<'MARKDOWN'
# Database Optimization Techniques

Database performance is crucial for web application success. Here are proven techniques to optimize your database operations.

## Indexing Strategies

### Primary Indexes
Every table should have a primary key with an index:

```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMP
);
```

### Composite Indexes
For queries with multiple WHERE conditions:

```sql
CREATE INDEX idx_posts_status_date ON posts (status, created_at);
```

### Covering Indexes
Include all columns needed for a query:

```sql
CREATE INDEX idx_posts_cover ON posts (status, created_at)
INCLUDE (title, excerpt);
```

## Query Optimization

### Use EXPLAIN
Analyze your queries:

```sql
EXPLAIN SELECT * FROM posts
WHERE status = 'published'
ORDER BY created_at DESC
LIMIT 10;
```

### Avoid N+1 Queries
Use eager loading in Laravel:

```php
// Bad: N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;
}

// Good: 2 queries total
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name;
}
```

## Database Design

### Normalization
- **1NF**: Eliminate repeating groups
- **2NF**: Remove partial dependencies
- **3NF**: Remove transitive dependencies

### Denormalization
Sometimes denormalization improves performance:

```php
// Store calculated values
class Post extends Model
{
    protected $fillable = [
        'title', 'content', 'comments_count'
    ];

    // Update count when comments change
    public function incrementCommentsCount()
    {
        $this->increment('comments_count');
    }
}
```

## Caching Strategies

### Query Result Caching
```php
$posts = Cache::remember('recent_posts', 3600, function () {
    return Post::published()
               ->with('author', 'categories')
               ->latest()
               ->take(10)
               ->get();
});
```

### Database Connection Pooling
Configure connection pooling for high-traffic applications.

## Monitoring

### Slow Query Log
Enable slow query logging:

```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

### Laravel Telescope
Monitor database queries in development:

```bash
composer require laravel/telescope
php artisan telescope:install
```

Proper database optimization can improve your application performance by 10x or more!
MARKDOWN;
    }

    /**
     * Get TDD content.
     */
    public static function getTddContent(): string
    {
        return <<<'MARKDOWN'
# Introduction to Test-Driven Development (TDD)

Test-Driven Development (TDD) is a software development approach where you write tests before writing the actual code. This methodology leads to better code quality, fewer bugs, and more maintainable applications.

## The TDD Cycle

TDD follows a simple three-step cycle:

### 1. Red: Write a Failing Test
Write a test for the functionality you want to implement:

```php
public function test_user_can_create_post()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
                     ->post('/posts', [
                         'title' => 'My First Post',
                         'content' => 'This is my first post content.'
                     ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('posts', [
        'title' => 'My First Post',
        'user_id' => $user->id
    ]);
}
```

### 2. Green: Make the Test Pass
Write the minimal code to make the test pass:

```php
class PostController extends Controller
{
    public function store(Request $request)
    {
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => auth()->id()
        ]);

        return response()->json($post, 201);
    }
}
```

### 3. Refactor: Improve the Code
Clean up and optimize while keeping tests green:

```php
public function store(StorePostRequest $request)
{
    $post = auth()->user()->posts()->create($request->validated());

    return new PostResource($post);
}
```

## Benefits of TDD

### Better Code Quality
- Forces you to think about design before implementation
- Results in more modular, testable code
- Reduces coupling between components

### Fewer Bugs
- Catches issues early in development
- Provides safety net for refactoring
- Documents expected behavior

### Faster Development
- Reduces debugging time
- Enables confident refactoring
- Provides immediate feedback

## TDD Best Practices

### Write Descriptive Test Names
```php
// Good
public function test_user_cannot_delete_post_they_do_not_own()

// Bad
public function test_delete_post()
```

### Test One Thing at a Time
Each test should verify a single behavior or outcome.

### Use Arrange-Act-Assert Pattern
```php
public function test_user_can_update_their_post()
{
    // Arrange
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    // Act
    $response = $this->actingAs($user)
                     ->put("/posts/{$post->id}", [
                         'title' => 'Updated Title'
                     ]);

    // Assert
    $response->assertStatus(200);
    $this->assertEquals('Updated Title', $post->fresh()->title);
}
```

## Common Pitfalls

### Testing Implementation Details
Focus on behavior, not implementation.

### Writing Tests After Code
This defeats the purpose of TDD.

### Skipping the Refactor Step
Always clean up your code while tests are green.

## Getting Started

1. Start with a simple feature
2. Write a failing test
3. Make it pass with minimal code
4. Refactor and repeat

TDD takes practice, but the benefits are worth the investment!
MARKDOWN;
    }

    /**
     * Get Docker content.
     */
    public static function getDockerContent(): string
    {
        return <<<'MARKDOWN'
# Docker for Developers

Docker revolutionizes how we develop, ship, and run applications. Learn how to containerize your applications and create consistent development environments.

## What is Docker?

Docker is a containerization platform that packages applications and their dependencies into lightweight, portable containers.

## Basic Concepts

### Images
Templates for creating containers:

```dockerfile
FROM php:8.2-fpm
WORKDIR /var/www
COPY . .
RUN composer install
EXPOSE 9000
CMD ["php-fpm"]
```

### Containers
Running instances of images:

```bash
docker run -d --name my-app -p 8000:8000 my-app-image
```

## Docker Compose

Manage multi-container applications:

```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www
    depends_on:
      - database

  database:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: laravel
      MYSQL_ROOT_PASSWORD: secret
    ports:
      - "3306:3306"
```

## Development Workflow

1. **Build**: Create your application image
2. **Run**: Start containers with docker-compose
3. **Develop**: Make changes with volume mounts
4. **Test**: Run tests in isolated containers

## Best Practices

- Use multi-stage builds for smaller images
- Leverage Docker layer caching
- Use .dockerignore to exclude unnecessary files
- Run containers as non-root users

Docker makes development environments consistent and deployment predictable!
MARKDOWN;
    }

    /**
     * Get Vue Composition API content.
     */
    public static function getVueCompositionContent(): string
    {
        return <<<'MARKDOWN'
# Vue.js 3 Composition API

The Composition API is Vue 3's new way to organize component logic. It provides better code reuse, type inference, and logic organization.

## Setup Function

The composition API centers around the setup function:

```javascript
import { ref, computed, onMounted } from 'vue'

export default {
  setup() {
    const count = ref(0)
    const doubleCount = computed(() => count.value * 2)

    function increment() {
      count.value++
    }

    onMounted(() => {
      console.log('Component mounted!')
    })

    return {
      count,
      doubleCount,
      increment
    }
  }
}
```

## Reactive References

### ref()
For primitive values:

```javascript
const message = ref('Hello')
const count = ref(0)
const isVisible = ref(true)

// Access with .value
console.log(message.value) // 'Hello'
message.value = 'World'
```

### reactive()
For objects:

```javascript
const state = reactive({
  user: {
    name: 'John',
    email: 'john@example.com'
  },
  posts: []
})

// Direct access
console.log(state.user.name) // 'John'
state.user.name = 'Jane'
```

## Computed Properties

```javascript
const firstName = ref('John')
const lastName = ref('Doe')

const fullName = computed(() => {
  return `${firstName.value} ${lastName.value}`
})
```

## Lifecycle Hooks

```javascript
import { onMounted, onUpdated, onUnmounted } from 'vue'

export default {
  setup() {
    onMounted(() => {
      console.log('Component mounted')
    })

    onUpdated(() => {
      console.log('Component updated')
    })

    onUnmounted(() => {
      console.log('Component unmounted')
    })
  }
}
```

## Composables

Create reusable logic:

```javascript
// composables/useCounter.js
import { ref } from 'vue'

export function useCounter(initialValue = 0) {
  const count = ref(initialValue)

  function increment() {
    count.value++
  }

  function decrement() {
    count.value--
  }

  function reset() {
    count.value = initialValue
  }

  return {
    count,
    increment,
    decrement,
    reset
  }
}
```

Use in components:

```javascript
import { useCounter } from '@/composables/useCounter'

export default {
  setup() {
    const { count, increment, decrement } = useCounter(10)

    return {
      count,
      increment,
      decrement
    }
  }
}
```

The Composition API makes Vue.js more powerful and flexible than ever!
MARKDOWN;
    }

    /**
     * Get career tips content.
     */
    public static function getCareerTipsContent(): string
    {
        return <<<'MARKDOWN'
# Career Growth Tips for Software Developers

Building a successful career in software development requires more than just coding skills. Here are practical tips to advance your career.

## Technical Skills

### Master the Fundamentals
- **Data Structures & Algorithms**: Essential for problem-solving
- **System Design**: Understand how large systems work
- **Database Design**: Learn SQL and NoSQL databases
- **Version Control**: Master Git workflows

### Stay Current
- Follow industry blogs and newsletters
- Attend conferences and meetups
- Take online courses regularly
- Contribute to open source projects

## Soft Skills

### Communication
- Write clear documentation
- Present technical concepts to non-technical stakeholders
- Practice active listening in meetings
- Give constructive code reviews

### Leadership
- Mentor junior developers
- Lead technical discussions
- Take ownership of projects
- Make data-driven decisions

## Building Your Network

### Online Presence
- Maintain an active GitHub profile
- Write technical blog posts
- Engage on Twitter/LinkedIn
- Contribute to developer communities

### Offline Networking
- Attend local meetups
- Speak at conferences
- Join professional organizations
- Find a mentor

## Career Progression

### Junior to Mid-Level
- Focus on code quality and best practices
- Learn to work independently
- Understand business requirements
- Develop debugging skills

### Mid-Level to Senior
- Design system architecture
- Mentor other developers
- Lead technical initiatives
- Understand business impact

### Senior to Staff/Principal
- Drive technical strategy
- Influence across teams
- Solve complex technical problems
- Shape engineering culture

## Job Search Strategy

### Resume Tips
- Highlight impact, not just responsibilities
- Use metrics to quantify achievements
- Tailor resume for each application
- Keep it concise and relevant

### Interview Preparation
- Practice coding problems daily
- Prepare system design examples
- Research the company thoroughly
- Prepare thoughtful questions

### Negotiation
- Research market rates
- Consider total compensation
- Negotiate based on value provided
- Be prepared to walk away

## Continuous Learning

### Set Learning Goals
- Identify skill gaps
- Create learning plans
- Track progress regularly
- Apply new skills to projects

### Learning Resources
- Online platforms (Coursera, Udemy, Pluralsight)
- Technical books and documentation
- Podcasts and YouTube channels
- Hands-on projects and experiments

## Work-Life Balance

### Avoid Burnout
- Set boundaries between work and personal time
- Take regular breaks and vacations
- Maintain hobbies outside of coding
- Prioritize physical and mental health

### Remote Work Tips
- Create a dedicated workspace
- Establish routines and schedules
- Communicate proactively with team
- Invest in good equipment

Remember: Career growth is a marathon, not a sprint. Focus on continuous improvement and building meaningful relationships!
MARKDOWN;
    }
}
