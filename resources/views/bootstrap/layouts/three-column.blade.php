@extends('layouts.app')

@section('title', $title ?? 'Three Column Layout')

@section('content')
<div class="{{ $containerClass ?? 'container-fluid' }}">
    <div class="row">
        {{-- Left Sidebar --}}
        <div class="col-12 col-md-{{ $leftSidebarWidth ?? 3 }} order-md-1">
            <aside role="complementary" class="{{ $leftSidebarClass ?? 'py-4' }}" aria-label="Left sidebar">
                @if(isset($leftSidebarTitle))
                    <h2 class="h5 mb-3">{{ $leftSidebarTitle }}</h2>
                @endif

                <div class="{{ $leftSidebarContentClass ?? '' }}">
                    {!! $leftSidebar ?? '' !!}
                </div>

                @if(isset($leftWidgets) && is_array($leftWidgets))
                    @foreach($leftWidgets as $widget)
                        <div class="card mb-3">
                            @if(isset($widget['title']))
                                <div class="card-header">
                                    <h3 class="card-title mb-0 h6">{{ $widget['title'] }}</h3>
                                </div>
                            @endif
                            
                            <div class="card-body py-2">
                                {!! $widget['content'] ?? '' !!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </aside>
        </div>

        {{-- Main Content Area --}}
        <div class="col-12 col-md-{{ $mainWidth ?? 6 }} order-md-2">
            <main role="main" class="{{ $mainContentClass ?? 'py-4' }}">
                @if(isset($breadcrumbs))
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="breadcrumb-item{{ $loop->last ? ' active' : '' }}">
                                    @if(!$loop->last && isset($breadcrumb['url']))
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
                                    @else
                                        {{ $breadcrumb['title'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                @if(isset($pageHeader))
                    <header class="mb-4">
                        @if(isset($pageHeader['title']))
                            <h1 class="h2 mb-3">{{ $pageHeader['title'] }}</h1>
                        @endif
                        
                        @if(isset($pageHeader['subtitle']))
                            <p class="lead text-muted">{{ $pageHeader['subtitle'] }}</p>
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
        <div class="col-12 col-md-{{ $rightSidebarWidth ?? 3 }} order-md-3">
            <aside role="complementary" class="{{ $rightSidebarClass ?? 'py-4' }}" aria-label="Right sidebar">
                @if(isset($rightSidebarTitle))
                    <h2 class="h5 mb-3">{{ $rightSidebarTitle }}</h2>
                @endif

                <div class="{{ $rightSidebarContentClass ?? '' }}">
                    {!! $rightSidebar ?? '' !!}
                </div>

                @if(isset($rightWidgets) && is_array($rightWidgets))
                    @foreach($rightWidgets as $widget)
                        <div class="card mb-3">
                            @if(isset($widget['title']))
                                <div class="card-header">
                                    <h3 class="card-title mb-0 h6">{{ $widget['title'] }}</h3>
                                </div>
                            @endif
                            
                            <div class="card-body py-2">
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
