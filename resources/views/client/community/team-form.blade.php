@extends('layout.novelight')
@section('template_title', $mode === 'create' ? __('messages.community.create_team_title') : __('messages.community.edit_team_title', ['name' => $item->name]))
@php
    $action = $mode === 'create' ? route('teams.store') : route('teams.update', $item->id);
@endphp

@push('styles')
@php $teamCssVer = file_exists(public_path('static/team/css/team.css')) ? filemtime(public_path('static/team/css/team.css')) : time(); @endphp
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}?v={{ $teamCssVer }}">
@endpush

@section('content')
<div class="team-page">
    <h1 class="team-title">{{ $mode === 'create' ? __('messages.community.create_team_title') : __('messages.community.edit_team_title', ['name' => $item->name]) }}</h1>

    <div class="team-layout">
        <main class="team-main team-panel">
            @if(session('success'))<div class="review-alert" style="background:#317a31">{{ session('success') }}</div>@endif
            @if($errors->any())
                <div class="review-alert" style="display:block;background:#a52a2a">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            @if($item->exists && $item->isPending())
                <div class="review-alert">
                    <i class="fa fa-info-circle"></i>
                    {{ __('messages.community.pending_review_notice') }}
                </div>
            @endif

            <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="team-form">
                @csrf
                @if($mode === 'edit') @method('patch') @endif

                <div class="frow">
                    <label>{{ __('messages.community.title') }}</label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required>
                </div>

                <div class="frow">
                    <label>{{ __('messages.community.image') }}</label>
                    <div class="team-photo-upload">
                        <div class="team-photo-preview">
                            <img id="team-photo-img"
                                 src="{{ old('photo_url', $item->photo ?: asset('static/core/images/no_cover.webp')) }}"
                                 alt="{{ $item->name ?? '' }}">
                        </div>
                        <div class="team-photo-controls">
                            <label for="team-photo-input" class="btn btn-invincible" style="cursor:pointer">{{ __('messages.community.choose_image') }}</label>
                            <input type="file" id="team-photo-input" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                            <span id="team-photo-name" style="font-size:12px;color:#666"></span>
                            <input type="text" name="photo_url" id="team-photo-url"
                                   value="{{ old('photo_url') }}"
                                   placeholder="{{ __('messages.community.paste_image_url') }}"
                                   style="flex:1;min-width:220px">
                        </div>
                    </div>
                </div>

                <div class="frow">
                    <label>{{ __('messages.community.description') }}:</label>
                    <textarea name="description">{{ old('description', $item->description) }}</textarea>
                </div>

                <div class="frow">
                    <label>{{ __('messages.community.site_url') }}</label>
                    <input type="text" name="site" value="{{ old('site', $item->site) }}" placeholder="https://...">
                </div>

                <div class="frow">
                    <label>{{ __('messages.community.donation_text_label') }}</label>
                    <input type="text" name="donation_text" value="{{ old('donation_text', $item->donation_text) }}">
                </div>

                <div class="frow">
                    <label>{{ __('messages.community.donation_url_label') }}</label>
                    <input type="text" name="donation_url" value="{{ old('donation_url', $item->donation_url) }}" placeholder="https://...">
                </div>

                <div class="team-form-actions">
                    <button type="submit" class="btn">{{ $mode === 'create' ? __('messages.community.create') : __('messages.community.update') }}</button>
                    <a href="{{ $mode === 'edit' ? route('teams.show', $item->id) : route('teams.index') }}" class="btn btn-invincible">{{ __('messages.community.cancel') }}</a>
                </div>
            </form>
        </main>

        @if($mode === 'edit')
            @include('client.community._team_sidebar', ['team' => $item])
        @endif
    </div>
</div>

<script>
(function(){
    var input = document.getElementById('team-photo-input');
    var urlField = document.getElementById('team-photo-url');
    var img = document.getElementById('team-photo-img');
    var name = document.getElementById('team-photo-name');

    function setImg(src, fileName) {
        if (src) img.src = src;
        if (name) name.textContent = fileName || '';
    }

    if (input) input.addEventListener('change', function() {
        var file = input.files && input.files[0];
        if (!file) return;
        if (urlField) urlField.value = '';
        setImg(URL.createObjectURL(file), file.name);
    });

    if (urlField) urlField.addEventListener('input', function() {
        var value = urlField.value.trim();
        if (!value) return;
        if (input) input.value = '';
        setImg(value, '');
    });
})();
</script>
@endsection
