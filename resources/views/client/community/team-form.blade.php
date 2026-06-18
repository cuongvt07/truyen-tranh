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
                    <label>{{ __('messages.community.team_photo_upload') }}</label>
                    <div class="team-photo-upload">
                        <div class="team-photo-preview">
                            @if($item->photo)
                                <img id="team-photo-img" src="{{ $item->photo }}" alt="">
                            @else
                                <img id="team-photo-img" src="" alt="" hidden>
                                <span id="team-photo-ph"><i class="fa fa-image"></i></span>
                            @endif
                        </div>
                        <div class="team-photo-controls">
                            <label for="team-photo-input" class="btn btn-invincible" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                                <i class="fa fa-cloud-upload-alt"></i> Chọn ảnh
                            </label>
                            <input type="file" id="team-photo-input" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                            <span id="team-photo-name" style="font-size:12px;color:var(--meta-color)"></span>
                            <div style="margin-top:8px">
                                <input type="text" name="photo_url" id="team-photo-url"
                                       value="{{ old('photo_url') }}"
                                       placeholder="{{ __('messages.account.or_paste_url') }}"
                                       style="width:100%;padding:8px 12px;border-radius:5px;border:1px solid var(--input-border-color,#2a2a3e);background:var(--bg,#fff);color:inherit;font-size:14px">
                            </div>
                        </div>
                    </div>
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

.team-photo-upload { display:flex; gap:16px; align-items:flex-start; }
.team-photo-preview { width:100px; height:100px; border-radius:8px; overflow:hidden; flex-shrink:0;
    border:1px solid var(--input-border-color,#2a2a3e); background:var(--bg,#f5f5f5);
    display:flex; align-items:center; justify-content:center; }
.team-photo-preview img { width:100%; height:100%; object-fit:cover; }
.team-photo-preview #team-photo-ph { font-size:28px; color:var(--meta-color,#999); }
.team-photo-controls { flex:1; min-width:0; display:flex; flex-direction:column; gap:8px; }
</style>

<script>
(function(){
    var inp = document.getElementById('team-photo-input');
    var urlField = document.getElementById('team-photo-url');
    var img = document.getElementById('team-photo-img');
    var ph = document.getElementById('team-photo-ph');
    var nm = document.getElementById('team-photo-name');

    function setImg(src, name) {
        if (!img) return;
        if (src) { img.src = src; img.hidden = false; if (ph) ph.hidden = true; }
        else { img.hidden = true; if (ph) ph.hidden = false; }
        if (nm) nm.textContent = name || '';
    }

    if (inp) inp.addEventListener('change', function() {
        var f = inp.files && inp.files[0]; if (!f) return;
        if (urlField) urlField.value = '';
        var u = URL.createObjectURL(f);
        setImg(u, f.name);
    });

    if (urlField) urlField.addEventListener('input', function() {
        var v = urlField.value.trim();
        if (v) { if (inp) inp.value = ''; setImg(v, ''); }
        else setImg('', '');
    });
})();
</script>
@endsection
