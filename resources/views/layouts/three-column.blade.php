@extends('cms-blog-system::layouts.app')

@section('title', $title ?? 'Three Column Layout')

@section('content')
<div class="{{ $containerClass ?? (app('cms-blog-system')->isBootstrap() ? 'container-fluid' : 'w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8') }}">
    <div class="{{ app('cms-blog-system')->isBootstrap() ? 'row' : 'flex flex-col lg:flex-row gap-6' }}">
        {{-- Left Sidebar --}}
        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'col-12 col-md-' . ($leftSidebarWidth ?? 3) . ' order-md-1' : 'w-full lg:w-' . ($leftSidebarWidthTailwind ?? '1/4') . ' lg:order-1' }}">
            <aside role="complementary" class="{{ $leftSidebarClass ?? (app('cms-blog-system')->isBootstrap() ? 'py-4' : 'py-6') }}" aria-label="Left sidebar">
                @if(isset($leftSidebarTitle))
                    <h2 class="{{ app('cms-blog-system')->isBootstrap() ? 'h5 mb-3' : 'text-lg font-semibold text-gray-900 mb-4' }}">
                        {{ $leftSidebarTitle }}
                    </h2>
                @endif

                <div class="{{ $leftSidebarContentClass ?? '' }}">
                    {!! $leftSidebar ?? '' !!}
                </div>

                @if(isset($leftWidgets) && is_array($leftWidgets))
                    @foreach($leftWidgets as $widget)
                        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card mb-3' : 'bg-white rounded-lg shadow-sm p-4 mb-4' }}">
                            @if(isset($widget['title']))
                                <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card-header' : 'border-b border-gray-200 pb-2 mb-3' }}">
                                    <h3 class="{{ app('cms-blog-system')->isBootstrap() ? 'card-title mb-0 h6' : 'text-base font-medium text-gray-900' }}">
                                        {{ $widget['title'] }}
                                    </h3>
                                </div>
                            @endif
                            
                            <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card-body py-2' : '' }}">
                                {!! $widget['content'] ?? '' !!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </aside>
        </div>

        {{-- Main Content Area --}}
        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'col-12 col-md-' . ($mainWidth ?? 6) . ' order-md-2' : 'w-full lg:w-' . ($mainWidthTailwind ?? '1/2') . ' lg:order-2' }}">
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
                            <h1 class="{{ app('cms-blog-system')->isBootstrap() ? 'h2 mb-3' : 'text-2xl font-bold text-gray-900 mb-4' }}">
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

        {{-- Right Sidebar --}}
        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'col-12 col-md-' . ($rightSidebarWidth ?? 3) . ' order-md-3' : 'w-full lg:w-' . ($rightSidebarWidthTailwind ?? '1/4') . ' lg:order-3' }}">
            <aside role="complementary" class="{{ $rightSidebarClass ?? (app('cms-blog-system')->isBootstrap() ? 'py-4' : 'py-6') }}" aria-label="Right sidebar">
                @if(isset($rightSidebarTitle))
                    <h2 class="{{ app('cms-blog-system')->isBootstrap() ? 'h5 mb-3' : 'text-lg font-semibold text-gray-900 mb-4' }}">
                        {{ $rightSidebarTitle }}
                    </h2>
                @endif

                <div class="{{ $rightSidebarContentClass ?? '' }}">
                    {!! $rightSidebar ?? '' !!}
                </div>

                @if(isset($rightWidgets) && is_array($rightWidgets))
                    @foreach($rightWidgets as $widget)
                        <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card mb-3' : 'bg-white rounded-lg shadow-sm p-4 mb-4' }}">
                            @if(isset($widget['title']))
                                <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card-header' : 'border-b border-gray-200 pb-2 mb-3' }}">
                                    <h3 class="{{ app('cms-blog-system')->isBootstrap() ? 'card-title mb-0 h6' : 'text-base font-medium text-gray-900' }}">
                                        {{ $widget['title'] }}
                                    </h3>
                                </div>
                            @endif
                            
                            <div class="{{ app('cms-blog-system')->isBootstrap() ? 'card-body py-2' : '' }}">
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
    /* Three Column Layout Styles */
    .three-column-layout {
        min-height: calc(100vh - 200px);
    }
    
    @media (max-width: 768px) {
        .three-column-layout aside {
            margin-top: 1rem;
        }
        
        .three-column-layout .order-md-1 {
            order: 3;
        }
        
        .three-column-layout .order-md-2 {
            order: 1;
        }
        
        .three-column-layout .order-md-3 {
            order: 2;
        }
    }
    
    /* Sidebar styling */
    .sidebar-sticky {
        position: sticky;
        top: 2rem;
    }
</style>
@endpush
