@extends('layout.admin')

@section('template_title', __('messages.admin_comments.forum_title'))

@section('content')
    @include('admin.comments._manager', [
        'title' => __('messages.admin_comments.forum_title'),
        'commentType' => 'forum',
        'routePrefix' => 'admin.forum.comments',
        'filterName' => 'post_id',
        'filterOptions' => $sources,
    ])
@endsection
