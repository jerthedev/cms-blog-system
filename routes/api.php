<?php

use Illuminate\Support\Facades\Route;
use JTD\CMSBlogSystem\Http\Controllers\Api\BlogPostController;
use JTD\CMSBlogSystem\Http\Controllers\Api\CategoryController;
use JTD\CMSBlogSystem\Http\Controllers\Api\TagController;

/*
|--------------------------------------------------------------------------
| CMS Blog System API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the CMS Blog System package.
| These routes provide JSON API endpoints for blog content.
|
*/

Route::group([
    'prefix' => 'api/cms-blog',
    'middleware' => ['api'],
], function () {
    // Blog posts API
    Route::apiResource('posts', BlogPostController::class)->only(['index', 'show']);

    // Categories API
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

    // Tags API
    Route::apiResource('tags', TagController::class)->only(['index', 'show']);

    // Search API
    Route::get('search', [BlogPostController::class, 'search'])->name('api.blog.search');
});
