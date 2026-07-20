@extends('client.users.profile')
@section('template_title', __('messages.account.nav_collections'))

@section('user_content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
    <h2 class="user-tab-title" style="margin:0">{{ __('messages.account.nav_collections') }}</h2>
    @if($isMine ?? false)
        <a href="{{ route_path('collections.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('messages.account.create_collection') }}</a>
    @endif
</div>

@if($collections->isEmpty())
    <div class="block"><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
        <i class="fa fa-layer-group" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
        {{ __('messages.account.collections_empty') }}
    </div></div>
@else
    <div class="collections"><div class="collection-mini-grid">
        @foreach($collections as $c)
            <a href="{{ ($isMine ?? false) ? route_path('collections.edit', $c->id) : '#' }}" class="collection-item">
                <div class="collection__inner">
                    <div class="collection-name clamp clamp-1">{{ $c->name }} @if($c->is_private)<i class="fa fa-lock" style="font-size:11px"></i>@endif</div>
                    <div class="collection-author meta-color clamp clamp-1"><i class="fa fa-book"></i> {{ __('messages.account.story_count', ['count' => $c->articles_count]) }}</div>
                    <div class="collection-meta__books">
                        @foreach($c->articles()->limit(3)->get() as $a)
                            <div class="image image-cover lazy-load-bg"><img class="lazy-image" loading="eager" src="{{ novel_poster($a) }}" alt="{{ $a->title }}"></div>
                        @endforeach
                    </div>
                </div>
            </a>
        @endforeach
    </div></div>
@endif
@endsection
