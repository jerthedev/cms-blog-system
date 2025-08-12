@extends('cms-blog-system::layouts.app')

@section('title', $title ?? 'Two Column Layout')

@section('content')
<div class="{{ $containerClass ?? (app('cms-blog-system')->isBootstrap() ? 'container-fluid' : 'w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8') }}">
    <div class="{{ app('cms-blog-system')->isBootstrap() ? 'row' : 'flex flex-col lg:flex-row gap-6' }}">
        {{-- Main Content Area --}}
        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'col-12 col-md-' . ($mainWidth ?? 8) : 'w-full lg:w-' . ($mainWidthTailwind ?? '2/3') }}">
            <main role="main" class="{{ $mainContentClass ?? (app('cms-blog-system')->isBootstrap() ? 'py-4' : 'py-6') }}">
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
                    </header>
                @endif

                @include('cms-blog-system::partials.alerts')

                <div class="{{ $contentWrapperClass ?? '' }}">
                    {!! $mainContent ?? $content ?? $slot ?? '' !!}
                </div>
            </main>
        </div>

        {{-- Sidebar Area --}}
        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'col-12 col-md-' . ($sidebarWidth ?? 4) : 'w-full lg:w-' . ($sidebarWidthTailwind ?? '1/3') }}">
            <aside role="complementary" class="{{ $sidebarClass ?? (app('cms-blog-system')->isBootstrap() ? 'py-4' : 'py-6') }}">
                @if(isset($sidebarTitle))
                    <h2 class="{{ app('cms-blog-system')->isBootstrap() ? 'h4 mb-3' : 'text-xl font-semibold text-gray-900 mb-4' }}">
                        {{ $sidebarTitle }}
                    </h2>
                @endif

                <div class="{{ $sidebarContentClass ?? '' }}">
                    {!! $sidebarContent ?? '' !!}
                </div>

                @if(isset($widgets) && is_array($widgets))
                    @foreach($widgets as $widget)
                        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card mb-4' : 'bg-white rounded-lg shadow-sm p-6 mb-6' }}">
                            @if(isset($widget['title']))
                                <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card-header' : 'border-b border-gray-200 pb-3 mb-4' }}">
                                    <h3 class="{{ app('cms-blog-system')->isBootstrap() ? 'card-title mb-0' : 'text-lg font-medium text-gray-900' }}">
                                        {{ $widget['title'] }}
                                    </h3>
                                </div>
                            @endif
                            
                            <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card-body' : '' }}">
                                {!! $widget['content'] ?? '' !!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </aside>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Two Column Layout Styles */
    .two-column-layout {
        min-height: calc(100vh - 200px);
    }
    
    @media (max-width: 768px) {
        .two-column-layout aside {
            margin-top: 2rem;
        }
    }
    
    /* Sidebar styling */
    .sidebar-sticky {
        position: sticky;
        top: 2rem;
    }
</style>
@endpush
