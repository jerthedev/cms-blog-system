<?php

declare(strict_types=1);

namespace JTD\CMSBlogSystem\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blog Authentication Middleware
 *
 * Handles authentication for blog-related routes.
 * Currently a placeholder for future authentication requirements.
 *
 * @author Jeremy Fall <jerthedev@gmail.com>
 */
class BlogAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Currently no authentication required for public blog routes
        // This middleware can be extended for protected blog features

        return $next($request);
    }
}
