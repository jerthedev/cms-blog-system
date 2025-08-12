@extends('layouts.app')

@section('title', $title ?? 'Three Column Layout')

@section('content')
<div class="{{ $containerClass ?? 'w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' }}">
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Left Sidebar --}}
        <div class="w-full lg:w-{{ $leftSidebarWidthTailwind ?? '1/4' }} lg:order-1">
            <aside role="complementary" class="{{ $leftSidebarClass ?? 'py-6' }}" aria-label="Left sidebar">
                @if(isset($leftSidebarTitle))
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $leftSidebarTitle }}</h2>
                @endif

                <div class="{{ $leftSidebarContentClass ?? '' }}">
                    {!! $leftSidebar ?? '' !!}
                </div>

                @if(isset($leftWidgets) && is_array($leftWidgets))
                    @foreach($leftWidgets as $widget)
                        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                            @if(isset($widget['title']))
                                <div class="border-b border-gray-200 pb-2 mb-3">
                                    <h3 class="text-base font-medium text-gray-900">{{ $widget['title'] }}</h3>
                                </div>
                            @endif
                            
                            <div>
                                {!! $widget['content'] ?? '' !!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </aside>
        </div>

        {{-- Main Content Area --}}
        <div class="w-full lg:w-{{ $mainWidthTailwind ?? '1/2' }} lg:order-2">
            <main role="main" class="{{ $mainContentClass ?? 'py-6' }}">
                @if(isset($breadcrumbs))
                    <nav aria-label="breadcrumb" class="mb-6">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="{{ $loop->last ? 'text-gray-900' : 'hover:text-gray-900' }}">
                                    @if(!$loop->last && isset($breadcrumb['url']))
                                        <a href="{{ $breadcrumb['url'] }}" class="hover:underline">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                        <span class="mx-2">/</span>
                                    @else
                                        {{ $breadcrumb['title'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                @if(isset($pageHeader))
                    <header class="mb-6">
                        @if(isset($pageHeader['title']))
                            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $pageHeader['title'] }}</h1>
                        @endif
                        
                        @if(isset($pageHeader['subtitle']))
                            <p class="text-lg text-gray-600">{{ $pageHeader['subtitle'] }}</p>
                        @endif
                    </header>
                @endif

                @include('partials.alerts')

                <div class="{{ $contentWrapperClass ?? '' }}">
                    {!! $mainContent ?? $content ?? $slot ?? '' !!}
                </div>
            </main>
        </div>

        {{-- Right Sidebar --}}
        <div class="w-full lg:w-{{ $rightSidebarWidthTailwind ?? '1/4' }} lg:order-3">
            <aside role="complementary" class="{{ $rightSidebarClass ?? 'py-6' }}" aria-label="Right sidebar">
                @if(isset($rightSidebarTitle))
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $rightSidebarTitle }}</h2>
                @endif

                <div class="{{ $rightSidebarContentClass ?? '' }}">
                    {!! $rightSidebar ?? '' !!}
                </div>

                @if(isset($rightWidgets) && is_array($rightWidgets))
                    @foreach($rightWidgets as $widget)
                        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                            @if(isset($widget['title']))
                                <div class="border-b border-gray-200 pb-2 mb-3">
                                    <h3 class="text-base font-medium text-gray-900">{{ $widget['title'] }}</h3>
                                </div>
                            @endif
                            
                            <div>
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
        
        .three-column-layout .lg\\:order-1 {
            order: 3;
        }
        
        .three-column-layout .lg\\:order-2 {
            order: 1;
        }
        
        .three-column-layout .lg\\:order-3 {
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
