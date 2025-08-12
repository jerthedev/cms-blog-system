@extends('layouts.app')

@section('title', $title ?? 'Single Column Layout')

@section('content')
<div class="{{ $containerClass ?? 'container-fluid' }}">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <main role="main" class="{{ $contentClass ?? 'py-4' }}">
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
                        
                        @if(isset($pageHeader['meta']))
                            <div class="text-muted small mt-2">{{ $pageHeader['meta'] }}</div>
                        @endif
                    </header>
                @endif

                @include('partials.alerts')

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
