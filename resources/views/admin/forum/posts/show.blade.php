@extends('layout.admin')

@section('template_title', 'View Forum Post')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">{{ $post->title_en }}</h3>
            <div>
                <a href="{{ route('admin.forum.posts.edit', $post->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.forum.posts.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Category:</strong> {{ $post->category->title_en }}</p>
                <p><strong>Author:</strong> {{ $post->user->username ?? 'System' }}</p>
                <p><strong>Status:</strong>
                    @if($post->status == 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($post->status == 'approved')
                        <span class="badge badge-success">Approved</span>
                    @else
                        <span class="badge badge-danger">Rejected</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6">
                <p><strong>Created:</strong> {{ $post->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>Views:</strong> {{ $post->view_count }}</p>
                <p><strong>Comments:</strong> {{ $post->comment_count }}</p>
            </div>
        </div>

        <hr>

        <h5>Content (EN):</h5>
        <div class="content-preview">
            {!! nl2br(e($post->content_en)) !!}
        </div>

        @if($post->content_vi)
            <hr>
            <h5>Content (VI):</h5>
            <div class="content-preview">
                {!! nl2br(e($post->content_vi)) !!}
            </div>
        @endif

        @if($post->comments->count() > 0)
            <hr>
            <h5>Comments ({{ $post->comments->count() }}):</h5>
            @foreach($post->comments as $comment)
                <div class="card mb-2">
                    <div class="card-body">
                        <p><strong>{{ $comment->user->username ?? 'Unknown' }}</strong> <small class="text-muted">{{ $comment->created_at->format('Y-m-d H:i') }}</small></p>
                        <p>{{ $comment->content }}</p>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
