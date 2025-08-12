@extends('layouts.app')

@section('title', $post->title . ' - Preview')

@section('head')
    <meta name="robots" content="noindex, nofollow">
    <style>
        .preview-pattern {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(99, 102, 241, 0.05) 10px,
                rgba(99, 102, 241, 0.05) 20px
            );
        }
    </style>
@endsection

@section('content')
<!-- Preview Banner -->
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white sticky top-0 z-50 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-center space-x-3">
            @if($previewType === 'draft')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"/>
                </svg>
                <span class="font-semibold uppercase tracking-wide text-sm">Draft Preview</span>
            @elseif($previewType === 'scheduled')
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold uppercase tracking-wide text-sm">Scheduled Preview</span>
            @else
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                <span class="font-semibold uppercase tracking-wide text-sm">Preview Mode</span>
            @endif
        </div>
        <div class="text-center mt-2">
            <p class="text-sm opacity-90">This is a preview of your unpublished post. Only you can see this page.</p>
        </div>
    </div>
</div>

<div class="min-h-screen bg-gray-50 preview-pattern">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Preview Meta Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-700">Status:</span>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $post->status === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ ucfirst($post->status) }}
                    </span>
                </div>
                <div>
                    @if($post->published_at)
                        <span class="text-sm font-medium text-gray-700">
                            @if($post->status === 'scheduled')
                                Scheduled for:
                            @else
                                Published:
                            @endif
                        </span>
                        <span class="ml-2 text-sm text-gray-900">{{ $post->published_at->format('M j, Y \a\t g:i A') }}</span>
                    @else
                        <span class="text-sm font-medium text-gray-700">Created:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ $post->created_at->format('M j, Y \a\t g:i A') }}</span>
                    @endif
                </div>
            </div>
            @if($post->updated_at->gt($post->created_at))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <span class="text-sm font-medium text-gray-700">Last Updated:</span>
                    <span class="ml-2 text-sm text-gray-900">{{ $post->updated_at->format('M j, Y \a\t g:i A') }}</span>
                </div>
            @endif
        </div>

        <!-- Post Content -->
        <article class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-8">
                <header class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 leading-tight">{{ $post->title }}</h1>
                    
                    @if($post->excerpt)
                        <p class="mt-4 text-xl text-gray-600 leading-relaxed">{{ $post->excerpt }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-6 mt-6 text-sm text-gray-500">
                        @if($post->author)
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <span>By {{ $post->author->name }}</span>
                            </div>
                        @endif
                        
                        @if($post->reading_time)
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ $post->reading_time }} min read</span>
                            </div>
                        @endif

                        @if($post->tags->count() > 0)
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($post->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </header>

                @if($post->featured_image)
                    <div class="mb-8">
                        <img src="{{ $post->featured_image }}" 
                             alt="{{ $post->title }}" 
                             class="w-full h-auto rounded-lg shadow-sm">
                    </div>
                @endif

                <div class="prose prose-lg max-w-none">
                    {!! $post->rendered_content !!}
                </div>
            </div>

            <!-- Preview Actions -->
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Preview Actions</h3>
                        <p class="text-sm text-gray-500">What would you like to do with this post?</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if($post->status === 'draft')
                            <a href="{{ route('admin.posts.edit', $post) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                </svg>
                                Edit
                            </a>
                            <button type="button" 
                                    onclick="publishPost({{ $post->id }})"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Publish Now
                            </button>
                            <button type="button" 
                                    onclick="schedulePost({{ $post->id }})"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Schedule
                            </button>
                        @elseif($post->status === 'scheduled')
                            <a href="{{ route('admin.posts.edit', $post) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                </svg>
                                Edit
                            </a>
                            <button type="button" 
                                    onclick="publishPost({{ $post->id }})"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Publish Now
                            </button>
                            <button type="button" 
                                    onclick="reschedulePost({{ $post->id }})"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                Reschedule
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>

@push('scripts')
<script>
function publishPost(postId) {
    if (confirm('Are you sure you want to publish this post now?')) {
        fetch(`/admin/posts/${postId}/publish`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || `/blog/${data.slug}`;
            } else {
                alert('Failed to publish post: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while publishing the post.');
        });
    }
}

function schedulePost(postId) {
    const scheduleDate = prompt('Enter the publish date and time (YYYY-MM-DD HH:MM):');
    if (scheduleDate) {
        fetch(`/admin/posts/${postId}/schedule`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ publish_date: scheduleDate }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Post scheduled successfully!');
                location.reload();
            } else {
                alert('Failed to schedule post: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while scheduling the post.');
        });
    }
}

function reschedulePost(postId) {
    const newDate = prompt('Enter the new publish date and time (YYYY-MM-DD HH:MM):');
    if (newDate) {
        fetch(`/admin/posts/${postId}/reschedule`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ publish_date: newDate }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Post rescheduled successfully!');
                location.reload();
            } else {
                alert('Failed to reschedule post: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rescheduling the post.');
        });
    }
}
</script>
@endpush
@endsection
