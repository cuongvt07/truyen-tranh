@extends('layout.novelight')
@section('template_title', $mode === 'create' ? __('messages.community.create_collection') : __('messages.community.edit_collection'))
@php
    $action = $mode === 'create' ? route('collections.store') : route('collections.update', $item->id);
    $selected = $item->exists ? $item->articles->pluck('id')->all() : [];
@endphp

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px"><div class="container"><h1><i class="fa fa-layer-group"></i> {{ $mode === 'create' ? __('messages.community.create_collection') : __('messages.community.edit_collection') }}</h1></div></header>
    <div class="block" style="max-width:680px;margin:0 auto;padding:24px">
        @if($errors->any())<div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form method="post" action="{{ $action }}" class="story-form">
            @csrf @if($mode === 'edit') @method('patch') @endif
            <div class="frow"><label>{{ __('messages.community.collection_name') }} <span style="color:#e84040">*</span></label><input type="text" name="name" value="{{ old('name', $item->name) }}" required></div>
            <div class="frow"><label>{{ __('messages.community.description') }}</label><textarea name="description" rows="3">{{ old('description', $item->description) }}</textarea></div>
            <div class="frow"><label class="book-checklist-label" style="display:flex;align-items:center;gap:8px;color:inherit;font-weight:400">
                <input type="checkbox" name="is_private" value="1" {{ old('is_private', $item->is_private) ? 'checked' : '' }}> {{ __('messages.community.private_collection') }}
            </label></div>
            <div class="frow">
                <label>{{ __('messages.community.select_stories') }}</label>
                <input type="text" id="book-filter" placeholder="{{ __('messages.community.filter_by_name') }}" style="margin-bottom:8px">
                <div class="book-checklist" id="book-list">
                    @foreach($articles as $a)
                        <label data-name="{{ \Illuminate\Support\Str::lower($a->title) }}">
                            <input type="checkbox" name="books[]" value="{{ $a->id }}" {{ in_array($a->id, old('books', $selected)) ? 'checked' : '' }}>
                            {{ $a->title }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:10px"><button type="submit" class="btn btn-primary">{{ $mode === 'create' ? __('messages.community.create') : __('messages.community.save') }}</button><a href="{{ route('collections.index') }}" class="btn btn-invincible">{{ __('messages.community.cancel') }}</a></div>
        </form>
    </div>
</div>
@include('client.community._form_style')
<script>
document.getElementById('book-filter')?.addEventListener('input', function(){
    var q=this.value.toLowerCase();
    document.querySelectorAll('#book-list label').forEach(function(l){
        l.style.display = l.getAttribute('data-name').includes(q) ? 'flex' : 'none';
    });
});
</script>
@endsection
