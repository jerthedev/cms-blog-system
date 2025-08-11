@if(app('cms-blog-system')->isBootstrap())
<footer class="bg-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; {{ date('Y') }} {{ config('cms-blog-system.blog.title', 'Blog') }}. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-end">
                @if(config('cms-blog-system.rss.enabled', true))
                    <a href="{{ route('blog.rss') }}" class="text-muted me-3">
                        <i class="fas fa-rss"></i> RSS Feed
                    </a>
                @endif
                <small class="text-muted">Powered by CMS Blog System</small>
            </div>
        </div>
    </div>
</footer>
@else
<footer class="bg-gray-50 mt-12">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-600">&copy; {{ date('Y') }} {{ config('cms-blog-system.blog.title', 'Blog') }}. All rights reserved.</p>
            </div>
            <div class="flex items-center space-x-4">
                @if(config('cms-blog-system.rss.enabled', true))
                    <a href="{{ route('blog.rss') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3.429 2.571c0-.952.771-1.714 1.714-1.714s1.714.762 1.714 1.714-.771 1.714-1.714 1.714-1.714-.762-1.714-1.714zM.857 8.571c0-.476.381-.857.857-.857 3.333 0 6.286 2.952 6.286 6.286 0 .476-.381.857-.857.857s-.857-.381-.857-.857c0-2.381-1.905-4.286-4.286-4.286-.476 0-.857-.381-.857-.857zM.857 14.286c0-.476.381-.857.857-.857 1.429 0 2.571 1.143 2.571 2.571 0 .476-.381.857-.857.857s-.857-.381-.857-.857c0-.476-.381-.857-.857-.857-.476 0-.857-.381-.857-.857z"/>
                        </svg>
                        RSS Feed
                    </a>
                @endif
                <span class="text-sm text-gray-500">Powered by CMS Blog System</span>
            </div>
        </div>
    </div>
</footer>
@endif
