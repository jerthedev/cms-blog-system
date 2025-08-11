# CMS Blog System

A comprehensive Laravel CMS and Blog system package with AdminPanel integration.

## Features

- 📝 **Blog Management**: Complete blog system with posts, categories, and tags
- 🎨 **Framework Choice**: Support for Bootstrap or Tailwind CSS
- 🔧 **AdminPanel Integration**: Seamless integration with JTD AdminPanel
- 📱 **Responsive Design**: Mobile-first responsive templates
- 🔍 **SEO Optimized**: Built-in SEO features, meta tags, and structured data
- 📡 **RSS Feeds**: Automatic RSS feed generation
- 🖼️ **Media Support**: Featured images and content media using Spatie Media Library
- 📄 **Markdown Support**: Rich markdown content with CommonMark
- 🔍 **Search**: Built-in search functionality
- ⚡ **Performance**: Optimized queries and caching support

## Installation

Install the package via Composer:

```bash
composer require jerthedev/cms-blog-system
```

Run the installation command:

```bash
php artisan blog:install
```

This will:
- Publish configuration files
- Publish view templates and assets
- Run database migrations
- Set up your preferred CSS framework

### Media Library Setup (Optional)

For file upload and media management features, set up Spatie Media Library:

```bash
# Quick setup (recommended)
php artisan cms-blog:setup-media

# Or manual setup
composer require spatie/laravel-medialibrary
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```

Check setup status:
```bash
php artisan cms-blog:setup-media --check
```

> **Note**: Media library is optional. The blog system works without it, but featured images and file uploads won't be available.

## Configuration

The package configuration is published to `config/cms-blog-system.php`. Key settings include:

- **Framework Choice**: Choose between Bootstrap or Tailwind CSS
- **Route Configuration**: Customize blog routes and middleware
- **SEO Settings**: Configure meta tags, Open Graph, and Twitter Cards
- **RSS Configuration**: Set up RSS feed generation
- **Media Settings**: Configure image handling and conversions

## Usage

### Basic Usage

After installation, your blog will be available at `/blog` (configurable).

### AdminPanel Integration

If you have the JTD AdminPanel installed, blog management resources will be automatically registered:

- Blog Posts management
- Categories with hierarchy support
- Tags with usage tracking
- Dashboard widgets

### Creating Content

Use the AdminPanel to create and manage your blog content, or create content programmatically:

```php
use JTD\CMSBlogSystem\Models\BlogPost;

$post = BlogPost::create([
    'title' => 'My First Post',
    'slug' => 'my-first-post',
    'content' => '# Hello World\n\nThis is my first blog post!',
    'status' => 'published',
    'published_at' => now(),
]);
```

## Framework Support

The package supports both Bootstrap and Tailwind CSS. Choose your framework during installation or update the configuration:

```php
// config/cms-blog-system.php
'framework' => 'bootstrap', // or 'tailwind'
```

## Customization

### Views

Publish the views to customize templates:

```bash
php artisan vendor:publish --tag=cms-blog-system-views
```

Views will be published to `resources/views/vendor/cms-blog-system/`.

### Configuration

Publish the configuration to customize settings:

```bash
php artisan vendor:publish --tag=cms-blog-system-config
```

## Requirements

- PHP 8.1+
- Laravel 12.0+
- JTD AdminPanel (for admin features)

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Support

For support, please visit our [GitHub repository](https://github.com/jerthedev/cms-blog-system) or contact [jerthedev@gmail.com](mailto:jerthedev@gmail.com).
