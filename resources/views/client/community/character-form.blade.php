@extends('layout.novelight')
@section('template_title', $mode === 'create' ? __('messages.community.add_character') : __('messages.community.edit_character'))
@php $action = $mode === 'create' ? route('characters.store') : route('characters.update', $item->id); @endphp

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px"><div class="container"><h1><i class="fa fa-user-pen"></i> {{ $mode === 'create' ? __('messages.community.add_character') : __('messages.community.edit_character') }}</h1></div></header>
    <div class="block" style="max-width:640px;margin:0 auto;padding:24px">
        @if($errors->any())<div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="story-form">
            @csrf @if($mode === 'edit') @method('patch') @endif
            <div class="frow"><label>{{ __('messages.community.character_name') }} <span style="color:#e84040">*</span></label><input type="text" name="name" value="{{ old('name', $item->name) }}" required></div>
            <div class="frow"><label>{{ __('messages.community.type') }}</label>
                <select name="type">
                    <option value="0" {{ old('type',$item->type)==0?'selected':'' }}>{{ __('messages.community.character_main') }}</option>
                    <option value="1" {{ old('type',$item->type)==1?'selected':'' }}>{{ __('messages.community.character_supporting') }}</option>
                    <option value="2" {{ old('type',$item->type)==2?'selected':'' }}>{{ __('messages.community.character_other') }}</option>
                </select>
            </div>
            <div class="frow">
                <x-image-upload name="photo" :label="__('messages.community.photo_upload')"
                    url-name="photo_url" :url-value="old('photo_url')"
                    :current="$item->photo ?: null" circle />
            </div>
            <div class="frow"><label>{{ __('messages.community.description') }}</label><textarea name="description" rows="5">{{ old('description', $item->description) }}</textarea></div>
            <div style="display:flex;gap:10px"><button type="submit" class="btn btn-primary">{{ $mode === 'create' ? __('messages.community.add') : __('messages.community.save') }}</button><a href="{{ route('characters.index') }}" class="btn btn-invincible">{{ __('messages.community.cancel') }}</a></div>
        </form>
    </div>
</div>
@include('client.community._form_style')
@endsection
