@extends('layouts.app')

@section('title', $title ?? 'Two Column Layout')

@section('content')
<div class="{{ $containerClass ?? 'w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' }}">
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Main Content Area --}}
        <div class="w-full lg:w-{{ $mainWidthTailwind ?? '2/3' }}">
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
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $pageHeader['title'] }}</h1>
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

        {{-- Sidebar Area --}}
        <div class="w-full lg:w-{{ $sidebarWidthTailwind ?? '1/3' }}">
            <aside role="complementary" class="{{ $sidebarClass ?? 'py-6' }}">
                @if(isset($sidebarTitle))
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $sidebarTitle }}</h2>
                @endif

                <div class="{{ $sidebarContentClass ?? '' }}">
                    {!! $sidebarContent ?? '' !!}
                </div>

                @if(isset($widgets) && is_array($widgets))
                    @foreach($widgets as $widget)
                        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                            @if(isset($widget['title']))
                                <div class="border-b border-gray-200 pb-3 mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $widget['title'] }}</h3>
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
