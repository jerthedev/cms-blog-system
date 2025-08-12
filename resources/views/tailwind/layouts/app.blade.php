<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('cms-blog-system.blog.title'))</title>
        <meta name="description" content="@yield('meta_description', config('cms-blog-system.blog.description'))">
        <meta name="keywords" content="@yield('meta_keywords', config('cms-blog-system.blog.keywords'))">
        <meta name="author" content="@yield('author', config('cms-blog-system.blog.author'))">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="@yield('og_title', config('cms-blog-system.blog.title'))">
        <meta property="og:description" content="@yield('og_description', config('cms-blog-system.blog.description'))">
        <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="@yield('twitter_title', config('cms-blog-system.blog.title'))">
        <meta name="twitter:description" content="@yield('twitter_description', config('cms-blog-system.blog.description'))">
        <meta name="twitter:image" content="@yield('twitter_image', asset('images/og-default.jpg'))">

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Custom Styles -->
        @stack('styles')
        
        <!-- Additional Head Content -->
        @yield('head')
    </head>
    <body class="@yield('body_class', 'bg-gray-50 text-gray-900')">
        <!-- Page Content -->
        @yield('content')

        <!-- Custom Scripts -->
        @stack('scripts')
        
        <!-- Additional Body Content -->
        @yield('scripts')
    </body>
</html>
