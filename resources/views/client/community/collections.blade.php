@extends('layout.novelight')
@section('template_title', __('messages.community.my_collections'))

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <h1><i class="fa fa-layer-group"></i> {{ __('messages.community.my_collections') }}</h1>
            <a href="{{ route('collections.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('messages.community.create_collection') }}</a>
        </div>
    </header>
    @if(session('success'))<div style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ session('success') }}</div>@endif

    <div class="block" style="padding:16px">
        @forelse($items as $c)
            <div class="my-story">
                <div class="my-story__info">
                    <div class="my-story__title">{{ $c->name }} @if($c->is_private)<i class="fa fa-lock" style="font-size:12px;color:var(--meta-color)" title="{{ __('messages.community.private') }}"></i>@endif</div>
                    <div class="meta-color" style="font-size:13px"><i class="fa fa-book"></i> {{ __('messages.community.stories_count', ['count' => $c->articles_count]) }}</div>
                </div>
                <div class="my-story__actions">
                    <a href="{{ route('collections.edit', $c->id) }}" class="btn btn-invincible"><i class="fa fa-edit"></i></a>
                    <form method="post" action="{{ route('collections.destroy', $c->id) }}" onsubmit="return confirm('{{ __('messages.community.delete_collection_confirm') }}')" style="display:inline">@csrf @method('delete')<button class="btn btn-invincible" style="color:#e84040"><i class="fa fa-trash"></i></button></form>
                </div>
            </div>
        @empty
            <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
                <i class="fa fa-layer-group" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
                {{ __('messages.community.no_collections') }} <a href="{{ route('collections.create') }}">{{ __('messages.community.create_now') }}</a>
            </div>
        @endforelse
        <div style="margin-top:16px">{{ $items->links('vendor.pagination.novelight') }}</div>
    </div>
</div>
<style>
.my-story { display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--border,#2a2a3e); }
.my-story__info { flex:1; min-width:0; } .my-story__title { font-weight:600; }
.my-story__actions { display:flex; gap:6px; } .my-story__actions .btn { padding:6px 10px; font-size:13px; }
</style>
@endsection
