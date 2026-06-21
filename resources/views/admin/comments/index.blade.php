@extends('layout.admin')

@section('template_title', __('messages.admin_comments.story_title'))

@section('content')
    @include('admin.comments._manager', [
        'title' => __('messages.admin_comments.story_title'),
        'commentType' => 'story',
        'routePrefix' => 'admin.comments',
        'filterName' => 'article_id',
        'filterOptions' => $articles,
    ])
@endsection
