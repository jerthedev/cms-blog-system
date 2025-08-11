@if(app('cms-blog-system')->isBootstrap())
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ route('blog.index') }}">
            {{ config('cms-blog-system.blog.title', 'Blog') }}
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('blog.index') }}">Home</a>
                </li>
            </ul>
            
            <form class="d-flex" method="GET" action="{{ route('blog.search') }}">
                <input class="form-control me-2" type="search" name="q" placeholder="Search posts..." value="{{ request('q') }}">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>
@else
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('blog.index') }}" class="text-xl font-bold text-gray-800">
                        {{ config('cms-blog-system.blog.title', 'Blog') }}
                    </a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="{{ route('blog.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Home
                    </a>
                </div>
            </div>
            
            <div class="flex items-center">
                <form method="GET" action="{{ route('blog.search') }}" class="flex">
                    <input type="search" name="q" placeholder="Search posts..." value="{{ request('q') }}" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="ml-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
@endif
