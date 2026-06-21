@extends('layout.admin')

@section('template_title', __('messages.admin_comments.faq_title'))

@section('content')
    @include('admin.comments._manager', [
        'title' => __('messages.admin_comments.faq_title'),
        'commentType' => 'faq',
        'routePrefix' => 'admin.faq.comments',
        'filterName' => 'article_id',
        'filterOptions' => $sources,
    ])
@endsection
