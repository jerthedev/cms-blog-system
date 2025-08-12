@extends('layouts.app')

@section('title', $post->title . ' - Preview')

@section('head')
    <meta name="robots" content="noindex, nofollow">
    <style>
        .draft-preview-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .preview-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .preview-content {
            position: relative;
        }
        
        .preview-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(102, 126, 234, 0.05) 10px,
                rgba(102, 126, 234, 0.05) 20px
            );
            pointer-events: none;
            z-index: 1;
        }
        
        .preview-meta {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 2rem;
        }
    </style>
@endsection

@section('content')
<div class="draft-preview-banner">
    <div class="container">
        <div class="preview-indicator">
            @if($previewType === 'draft')
                <i class="bi bi-file-earmark-text"></i>
                <span>Draft Preview</span>
            @elseif($previewType === 'scheduled')
                <i class="bi bi-clock"></i>
                <span>Scheduled Preview</span>
            @else
                <i class="bi bi-eye"></i>
                <span>Preview Mode</span>
            @endif
        </div>
        <div class="mt-2">
            <small>This is a preview of your unpublished post. Only you can see this page.</small>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="preview-content">
                <!-- Preview Meta Information -->
                <div class="preview-meta">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $post->status === 'draft' ? 'secondary' : 'primary' }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            @if($post->published_at)
                                <strong>
                                    @if($post->status === 'scheduled')
                                        Scheduled for:
                                    @else
                                        Published:
                                    @endif
                                </strong>
                                {{ $post->published_at->format('M j, Y \a\t g:i A') }}
                            @else
                                <strong>Created:</strong> {{ $post->created_at->format('M j, Y \a\t g:i A') }}
                            @endif
                        </div>
                    </div>
                    @if($post->updated_at->gt($post->created_at))
                        <div class="row mt-2">
                            <div class="col-12">
                                <strong>Last Updated:</strong> {{ $post->updated_at->format('M j, Y \a\t g:i A') }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Post Content -->
                <article class="blog-post">
                    <header class="mb-4">
                        <h1 class="display-4 fw-bold">{{ $post->title }}</h1>
                        
                        @if($post->excerpt)
                            <p class="lead text-muted">{{ $post->excerpt }}</p>
                        @endif

                        <div class="d-flex flex-wrap gap-3 text-muted small mt-3">
                            @if($post->author)
                                <div>
                                    <i class="bi bi-person"></i>
                                    By {{ $post->author->name }}
                                </div>
                            @endif
                            
                            @if($post->reading_time)
                                <div>
                                    <i class="bi bi-clock"></i>
                                    {{ $post->reading_time }} min read
                                </div>
                            @endif

                            @if($post->tags->count() > 0)
                                <div>
                                    <i class="bi bi-tags"></i>
                                    @foreach($post->tags as $tag)
                                        <span class="badge bg-light text-dark me-1">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </header>

                    @if($post->featured_image)
                        <div class="mb-4">
                            <img src="{{ $post->featured_image }}" 
                                 alt="{{ $post->title }}" 
                                 class="img-fluid rounded">
                        </div>
                    @endif

                    <div class="post-content">
                        {!! $post->rendered_content !!}
                    </div>
                </article>

                <!-- Preview Actions -->
                <div class="mt-5 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Preview Actions</h6>
                            <small class="text-muted">What would you like to do with this post?</small>
                        </div>
                        <div class="btn-group" role="group">
                            @if($post->status === 'draft')
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <button type="button" class="btn btn-success btn-sm" onclick="publishPost({{ $post->id }})">
                                    <i class="bi bi-check-circle"></i> Publish Now
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="schedulePost({{ $post->id }})">
                                    <i class="bi bi-clock"></i> Schedule
                                </button>
                            @elseif($post->status === 'scheduled')
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <button type="button" class="btn btn-success btn-sm" onclick="publishPost({{ $post->id }})">
                                    <i class="bi bi-check-circle"></i> Publish Now
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="reschedulePost({{ $post->id }})">
                                    <i class="bi bi-calendar"></i> Reschedule
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function publishPost(postId) {
    if (confirm('Are you sure you want to publish this post now?')) {
        // This would make an AJAX call to publish the post
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
        // This would make an AJAX call to schedule the post
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
        // This would make an AJAX call to reschedule the post
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
