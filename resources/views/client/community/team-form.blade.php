@extends('layout.novelight')
@section('template_title', $mode === 'create' ? __('messages.community.create_team') : ('Cập nhật thông tin — ' . $item->name))
@php $action = $mode === 'create' ? route('teams.store') : route('teams.update', $item->id); @endphp

@section('content')
<div class="container">
    <h1 class="page-title">{{ $mode === 'create' ? __('messages.community.create_team') : 'Cập nhật thông tin — ' . $item->name }}</h1>

    <div class="flex-content">
        <div class="main block">
            @if(session('success'))<div style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ session('success') }}</div>@endif
            @if($errors->any())
                <div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="story-form">
                @csrf @if($mode === 'edit') @method('patch') @endif
                <div class="frow">
                    <label>{{ __('messages.community.team_name') }} <span style="color:#e84040">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required>
                </div>
                <div class="frow">
                    <x-image-upload name="photo" :label="__('messages.community.team_photo_upload')"
                        url-name="photo_url" :url-value="old('photo_url')"
                        :current="$item->photo ?: null" />
                </div>
                <div class="frow">
                    <label>{{ __('messages.community.description') }}</label>
                    <textarea name="description" rows="5">{{ old('description', $item->description) }}</textarea>
                </div>
                <div class="frow">
                    <label>Website</label>
                    <input type="text" name="site" value="{{ old('site', $item->site) }}" placeholder="https://...">
                </div>
                <div class="frow">
                    <label>{{ __('messages.community.donation_text') }}</label>
                    <input type="text" name="donation_text" value="{{ old('donation_text', $item->donation_text) }}" placeholder="Patreon / ko-fi">
                </div>
                <div class="frow">
                    <label>{{ __('messages.community.donation_url') }}</label>
                    <input type="text" name="donation_url" value="{{ old('donation_url', $item->donation_url) }}" placeholder="https://...">
                </div>
                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn">
                        {{ $mode === 'create' ? __('messages.community.create_team_btn') : __('messages.community.save') }}
                    </button>
                    <a href="{{ route('teams.index') }}" class="btn btn-invincible">{{ __('messages.community.cancel') }}</a>
                </div>
            </form>
        </div>

        @if($mode === 'edit')
            @include('client.community._team_sidebar')
        @endif
    </div>
</div>

@include('client.community._form_style')

<style>
.flex-content { display:flex; gap:20px; align-items:flex-start; }
.flex-content .main { flex:1; min-width:0; }
.second-information { width:200px; flex-shrink:0; }
.btn-list { display:flex; flex-direction:column; gap:6px; padding:16px; }
.btn-list .btn, .btn-list .btn-invincible { display:block; text-align:center; }
.page-title { font-size:22px; font-weight:700; margin-bottom:20px; }
@media(max-width:640px) { .flex-content { flex-direction:column; } .second-information { width:100%; } }
</style>
@endsection
