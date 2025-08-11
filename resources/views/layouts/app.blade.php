<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('cms-blog-system.blog.title', 'Blog'))</title>
    
    @if(config('cms-blog-system.seo.enabled', true))
        <meta name="description" content="@yield('meta_description', config('cms-blog-system.blog.description'))">
        <meta name="keywords" content="@yield('meta_keywords')">
        
        @if(config('cms-blog-system.seo.open_graph', true))
            <meta property="og:title" content="@yield('og_title', '@yield('title', config('cms-blog-system.blog.title'))')">
            <meta property="og:description" content="@yield('og_description', '@yield('meta_description', config('cms-blog-system.blog.description'))')">
            <meta property="og:type" content="@yield('og_type', 'website')">
            <meta property="og:url" content="{{ url()->current() }}">
            @yield('og_image')
        @endif
        
        @if(config('cms-blog-system.seo.twitter_cards', true))
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="@yield('twitter_title', '@yield('title', config('cms-blog-system.blog.title'))')">
            <meta name="twitter:description" content="@yield('twitter_description', '@yield('meta_description', config('cms-blog-system.blog.description'))')">
            @yield('twitter_image')
        @endif
    @endif

    @if(app('cms-blog-system')->isBootstrap())
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @elseif(app('cms-blog-system')->isTailwind())
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    @stack('styles')
</head>
<body class="@yield('body_class')">
    <div id="app">
        @include('cms-blog-system::partials.header')
        
        <main class="@if(app('cms-blog-system')->isBootstrap()) container @else max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 @endif">
            @yield('content')
        </main>
        
        @include('cms-blog-system::partials.footer')
    </div>

    @if(app('cms-blog-system')->isBootstrap())
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @endif

    @stack('scripts')
</body>
</html>
