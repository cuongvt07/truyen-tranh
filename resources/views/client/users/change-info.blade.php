@extends('client.users.profile')
@section('template_title', __('messages.account.nav_settings'))

@section('user_content')
{{-- Tabs: Thông tin / Bảo mật --}}
<div class="block list-names" style="margin-bottom:14px">
    <a href="{{ route('users.change_info') }}" class="btn">{{ __('messages.account.tab_info') }}</a>
    <a href="{{ route('users.change_password') }}" class="btn btn-invincible">{{ __('messages.account.tab_security') }}</a>
</div>

<div class="block">
    @if(session('status'))
        <div class="alert-success" style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="error-block" style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form action="{{ route('users.update') }}" method="post" enctype="multipart/form-data" class="settings-form">
        @csrf @method('patch')

        {{-- Avatar --}}
        <div class="form-row">
            <label class="form-label">{{ __('messages.account.avatar') }}</label>
            <div class="img-upload">
                <div class="img-preview" style="border-radius:50%">
                    <img src="{{ $user->avatar ?: asset('static/account/images/no-ava.jpg') }}" alt="">
                </div>
                <input type="file" name="avatar" accept="image/*">
            </div>
        </div>

        {{-- Background --}}
        <div class="form-row">
            <label class="form-label">{{ __('messages.account.background') }}</label>
            <div class="img-upload">
                <div class="img-preview wide">
                    <img src="{{ $user->background ?: ($user->avatar ?: asset('static/core/images/no_cover.webp')) }}" alt="">
                </div>
                <input type="file" name="background" accept="image/*">
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">{{ __('messages.account.username') }}</label>
            <input type="text" name="username" value="{{ old('username', $user->username) }}" required>
        </div>
        <div class="form-row">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="form-row">
            <label class="form-label">{{ __('messages.account.full_name') }}</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}">
        </div>
        <div class="form-row">
            <label class="form-label">{{ __('messages.account.gender') }}</label>
            <select name="gender">
                @foreach(\App\Enums\Gender::cases() as $g)
                    <option value="{{ $g->value }}" {{ $user->gender == $g->value ? 'selected' : '' }}>{{ $g->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-row">
            <label class="form-label">{{ __('messages.account.date_of_birth') }}</label>
            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}">
        </div>
        <div class="form-row">
            <label class="form-label">{{ __('messages.account.about_me') }}</label>
            <textarea name="description" rows="5">{{ old('description', $user->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.account.save_changes') }}</button>
    </form>
</div>

<style>
.settings-form .form-row { margin-bottom:16px; }
.settings-form .form-label { display:block; font-size:13px; margin-bottom:6px; color:var(--meta-color,#999); font-weight:500; }
.settings-form input[type=text], .settings-form input[type=email], .settings-form input[type=date],
.settings-form select, .settings-form textarea {
    width:100%; max-width:480px; padding:9px 12px; border-radius:5px; border:1px solid var(--border,#2a2a3e);
    background:var(--bg,#131320); color:inherit; font-size:14px;
}
.settings-form .img-upload { display:flex; align-items:center; gap:14px; }
.settings-form .img-preview { width:80px; height:80px; overflow:hidden; border:1px solid var(--border,#2a2a3e); border-radius:8px; flex-shrink:0; }
.settings-form .img-preview.wide { width:160px; height:80px; }
.settings-form .img-preview img { width:100%; height:100%; object-fit:cover; }
.list-names .btn { text-decoration:none; }
</style>
@endsection
