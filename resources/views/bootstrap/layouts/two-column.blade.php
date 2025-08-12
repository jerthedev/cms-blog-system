@extends('layouts.app')

@section('title', $title ?? 'Two Column Layout')

@section('content')
<div class="{{ $containerClass ?? 'container-fluid' }}">
    <div class="row">
        {{-- Main Content Area --}}
        <div class="col-12 col-md-{{ $mainWidth ?? 8 }}">
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
                            <h1 class="h1 mb-3">{{ $pageHeader['title'] }}</h1>
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

        {{-- Sidebar Area --}}
        <div class="col-12 col-md-{{ $sidebarWidth ?? 4 }}">
            <aside role="complementary" class="{{ $sidebarClass ?? 'py-4' }}">
                @if(isset($sidebarTitle))
                    <h2 class="h4 mb-3">{{ $sidebarTitle }}</h2>
                @endif

                <div class="{{ $sidebarContentClass ?? '' }}">
                    {!! $sidebarContent ?? '' !!}
                </div>

                @if(isset($widgets) && is_array($widgets))
                    @foreach($widgets as $widget)
                        <div class="card mb-4">
                            @if(isset($widget['title']))
                                <div class="card-header">
                                    <h3 class="card-title mb-0">{{ $widget['title'] }}</h3>
                                </div>
                            @endif
                            
                            <div class="card-body">
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
