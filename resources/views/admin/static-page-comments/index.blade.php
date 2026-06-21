@extends('layout.admin')

@section('template_title', __('messages.admin_comments.static_title'))

@section('content')
    @if($schemaWarning ?? false)
        <div class="content pt-3">
            <div class="container-fluid">
                <div class="alert alert-warning mb-0">{{ __('messages.admin_comments.schema_warning') }}</div>
            </div>
        </div>
    @endif

    @include('admin.comments._manager', [
        'title' => __('messages.admin_comments.static_title'),
        'commentType' => 'static',
        'routePrefix' => 'admin.static_page_comments',
        'filterName' => 'page_type',
        'filterOptions' => $pageTypes,
        'supportsHidden' => $supportsHidden ?? false,
    ])
@endsection
