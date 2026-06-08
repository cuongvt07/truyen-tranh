@extends('layout.novelight')
@section('template_title', $mode === 'create' ? __('messages.community.create_team') : __('messages.community.edit_team'))
@php $action = $mode === 'create' ? route('teams.store') : route('teams.update', $item->id); @endphp

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px"><div class="container"><h1><i class="fa fa-user-friends"></i> {{ $mode === 'create' ? __('messages.community.create_team') : __('messages.community.edit_team') }}</h1></div></header>
    <div class="block" style="max-width:640px;margin:0 auto;padding:24px">
        @if($errors->any())<div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="story-form">
            @csrf @if($mode === 'edit') @method('patch') @endif
            <div class="frow"><label>{{ __('messages.community.team_name') }} <span style="color:#e84040">*</span></label><input type="text" name="name" value="{{ old('name', $item->name) }}" required></div>
            <div class="frow"><label>{{ __('messages.community.team_photo_upload') }}</label><input type="file" name="photo" accept="image/*"></div>
            <div class="frow"><label>{{ __('messages.community.photo_url') }}</label><input type="text" name="photo_url" placeholder="https://..."></div>
            @if($item->exists && $item->photo)<div class="frow"><img src="{{ $item->photo }}" style="width:80px;border-radius:8px"></div>@endif
            <div class="frow"><label>{{ __('messages.community.description') }}</label><textarea name="description" rows="4">{{ old('description', $item->description) }}</textarea></div>
            <div class="frow"><label>Website</label><input type="text" name="site" value="{{ old('site', $item->site) }}" placeholder="https://..."></div>
            <div class="frow"><label>{{ __('messages.community.donation_text') }}</label><input type="text" name="donation_text" value="{{ old('donation_text', $item->donation_text) }}"></div>
            <div class="frow"><label>{{ __('messages.community.donation_url') }}</label><input type="text" name="donation_url" value="{{ old('donation_url', $item->donation_url) }}" placeholder="https://..."></div>
            <div style="display:flex;gap:10px"><button type="submit" class="btn btn-primary">{{ $mode === 'create' ? __('messages.community.create_team_btn') : __('messages.community.save') }}</button><a href="{{ route('teams.index') }}" class="btn btn-invincible">{{ __('messages.community.cancel') }}</a></div>
        </form>
    </div>
</div>
@include('client.community._form_style')
@endsection
