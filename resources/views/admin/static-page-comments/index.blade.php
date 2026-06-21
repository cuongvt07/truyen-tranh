@extends('layout.admin')

@section('template_title', __('messages.admin_comments.static_title'))

@section('content')
    @include('admin.comments._manager', [
        'title' => __('messages.admin_comments.static_title'),
        'commentType' => 'static',
        'routePrefix' => 'admin.static_page_comments',
        'filterName' => 'page_type',
        'filterOptions' => $pageTypes,
    ])
@endsection
