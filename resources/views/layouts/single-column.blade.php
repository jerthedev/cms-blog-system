@extends('cms-blog-system::layouts.app')

@section('title', $title ?? 'Single Column Layout')

@section('content')
<div class="{{ $containerClass ?? (app('cms-blog-system')->isBootstrap() ? 'container-fluid' : 'w-full') }}">
    <div class="{{ app('cms-blog-system')->isBootstrap() ? 'row justify-content-center' : 'flex justify-center' }}">
        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'col-12 col-lg-10 col-xl-8' : 'w-full lg:w-5/6 xl:w-2/3' }}">
            <main role="main" class="{{ $contentClass ?? (app('cms-blog-system')->isBootstrap() ? 'py-4' : 'py-6') }}">
                @if(isset($breadcrumbs))
                    <nav aria-label="breadcrumb" class="{{ app('cms-blog-system')->isBootstrap() ? 'mb-4' : 'mb-6' }}">
                        <ol class="{{ app('cms-blog-system')->isBootstrap() ? 'breadcrumb' : 'flex space-x-2 text-sm text-gray-600' }}">
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="{{ app('cms-blog-system')->isBootstrap() ? 'breadcrumb-item' . ($loop->last ? ' active' : '') : ($loop->last ? 'text-gray-900' : 'hover:text-gray-900') }}">
                                    @if(!$loop->last && isset($breadcrumb['url']))
                                        <a href="{{ $breadcrumb['url'] }}" class="{{ app('cms-blog-system')->isTailwind() ? 'hover:underline' : '' }}">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                        @if(app('cms-blog-system')->isTailwind())
                                            <span class="mx-2">/</span>
                                        @endif
                                    @else
                                        {{ $breadcrumb['title'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                @if(isset($pageHeader))
                    <header class="{{ app('cms-blog-system')->isBootstrap() ? 'mb-4' : 'mb-6' }}">
                        @if(isset($pageHeader['title']))
                            <h1 class="{{ app('cms-blog-system')->isBootstrap() ? 'h1 mb-3' : 'text-3xl font-bold text-gray-900 mb-4' }}">
                                {{ $pageHeader['title'] }}
                            </h1>
                        @endif
                        
                        @if(isset($pageHeader['subtitle']))
                            <p class="{{ app('cms-blog-system')->isBootstrap() ? 'lead text-muted' : 'text-lg text-gray-600' }}">
                                {{ $pageHeader['subtitle'] }}
                            </p>
                        @endif
                        
                        @if(isset($pageHeader['meta']))
                            <div class="{{ app('cms-blog-system')->isBootstrap() ? 'text-muted small mt-2' : 'text-sm text-gray-500 mt-2' }}">
                                {{ $pageHeader['meta'] }}
                            </div>
                        @endif
                    </header>
                @endif

                @if(session('success'))
                    <div class="{{ app('cms-blog-system')->isBootstrap() ? 'alert alert-success alert-dismissible fade show' : 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4' }}" role="alert">
                        {{ session('success') }}
                        @if(app('cms-blog-system')->isBootstrap())
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        @else
                            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                                <span class="sr-only">Close</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                @if(session('error'))
                    <div class="{{ app('cms-blog-system')->isBootstrap() ? 'alert alert-danger alert-dismissible fade show' : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4' }}" role="alert">
                        {{ session('error') }}
                        @if(app('cms-blog-system')->isBootstrap())
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        @else
                            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                                <span class="sr-only">Close</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                <div class="{{ $contentWrapperClass ?? '' }}">
                    {!! $content ?? $slot ?? '' !!}
                </div>
            </main>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Single Column Layout Styles */
    .single-column-layout {
        min-height: calc(100vh - 200px);
    }
    
    @media (max-width: 768px) {
        .single-column-layout {
            padding: 1rem;
        }
    }
</style>
@endpush
