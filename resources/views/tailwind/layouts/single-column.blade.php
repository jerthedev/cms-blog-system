@extends('layouts.app')

@section('title', $title ?? 'Single Column Layout')

@section('content')
<div class="{{ $containerClass ?? 'w-full' }}">
    <div class="flex justify-center">
        <div class="w-full lg:w-5/6 xl:w-2/3">
            <main role="main" class="{{ $contentClass ?? 'py-6' }}">
                @if(isset($breadcrumbs))
                    <nav aria-label="breadcrumb" class="mb-6">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="{{ $loop->last ? 'text-gray-900' : 'hover:text-gray-900' }}">
                                    @if(!$loop->last && isset($breadcrumb['url']))
                                        <a href="{{ $breadcrumb['url'] }}" class="hover:underline">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                        @if(!$loop->last)
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
                    <header class="mb-6">
                        @if(isset($pageHeader['title']))
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $pageHeader['title'] }}</h1>
                        @endif
                        
                        @if(isset($pageHeader['subtitle']))
                            <p class="text-lg text-gray-600">{{ $pageHeader['subtitle'] }}</p>
                        @endif
                        
                        @if(isset($pageHeader['meta']))
                            <div class="text-sm text-gray-500 mt-2">{{ $pageHeader['meta'] }}</div>
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
