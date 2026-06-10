@extends('layout.admin')

@section('template_title', 'FAQ Settings')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">FAQ Settings</h3>
    </div>
    <form action="{{ route('admin.faq.settings.update') }}" method="POST">
        @csrf
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="allow_guest_view" name="allow_guest_view" value="1" {{ $settings['allow_guest_view'] ?? true ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_guest_view">
                        <strong>Allow guest viewing</strong>
                        <p class="text-muted small">Non-logged-in users can view FAQ articles</p>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="enable_comments" name="enable_comments" value="1" {{ $settings['enable_comments'] ?? true ? 'checked' : '' }}>
                    <label class="form-check-label" for="enable_comments">
                        <strong>Enable comments globally</strong>
                        <p class="text-muted small">Allow comments on FAQ articles (can be overridden per article)</p>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="articles_per_page">Articles per page</label>
                <input type="number" class="form-control" id="articles_per_page" name="articles_per_page" value="{{ $settings['articles_per_page'] ?? 20 }}" min="5" max="100">
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
