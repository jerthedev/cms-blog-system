<?php

use Illuminate\Support\Facades\Route;
use JTD\CMSBlogSystem\Http\Controllers\BlogController;
use JTD\CMSBlogSystem\Http\Controllers\CategoryController;
use JTD\CMSBlogSystem\Http\Controllers\RSSController;
use JTD\CMSBlogSystem\Http\Controllers\TagController;

/*
|--------------------------------------------------------------------------
| CMS Blog System Web Routes
|--------------------------------------------------------------------------
|
| Here are the web routes for the CMS Blog System package.
| These routes handle the frontend display of blog content.
|
*/

Route::group([
    'prefix' => config('cms-blog-system.routes.prefix', 'blog'),
    'middleware' => config('cms-blog-system.routes.middleware', ['web']),
    'domain' => config('cms-blog-system.routes.domain'),
], function () {
    // Blog post routes
    Route::get('/', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/post/{slug}', [BlogController::class, 'show'])->name('blog.show');

    // Category routes
    Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('blog.category');

    // Tag routes
    Route::get('/tag/{slug}', [TagController::class, 'show'])->name('blog.tag');

    // RSS feed
    Route::get('/rss', [RSSController::class, 'index'])->name('blog.rss');

    // Search
    Route::get('/search', [BlogController::class, 'search'])->name('blog.search');
});
