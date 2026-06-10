@extends('layout.admin')

@section('template_title', 'Forum Settings')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Forum Settings</h3>
    </div>
    <form action="{{ route('admin.forum.settings.update') }}" method="POST">
        @csrf
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="auto_approve_posts" name="auto_approve_posts" value="1" {{ $settings['auto_approve_posts'] ?? false ? 'checked' : '' }}>
                    <label class="form-check-label" for="auto_approve_posts">
                        <strong>Auto-approve posts</strong>
                        <p class="text-muted small">New posts will be automatically approved without admin review</p>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="allow_guest_view" name="allow_guest_view" value="1" {{ $settings['allow_guest_view'] ?? true ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_guest_view">
                        <strong>Allow guest viewing</strong>
                        <p class="text-muted small">Non-logged-in users can view forum posts</p>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="posts_per_page">Posts per page</label>
                <input type="number" class="form-control" id="posts_per_page" name="posts_per_page" value="{{ $settings['posts_per_page'] ?? 20 }}" min="5" max="100">
            </div>

            <div class="form-group">
                <label for="comments_per_page">Comments per page</label>
                <input type="number" class="form-control" id="comments_per_page" name="comments_per_page" value="{{ $settings['comments_per_page'] ?? 50 }}" min="5" max="100">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection
