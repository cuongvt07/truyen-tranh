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
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" data-max-mb="4">
            </div>
            <div class="upload-hint">JPG, PNG, GIF, WebP · {{ __('messages.account.upload_max', ['size' => '4MB']) }}</div>
            <div class="upload-error" hidden></div>
        </div>

        {{-- Background --}}
        <div class="form-row">
            <label class="form-label">{{ __('messages.account.background') }}</label>
            <div class="img-upload">
                <div class="img-preview wide">
                    <img src="{{ $user->background ?: ($user->avatar ?: asset('static/core/images/no_cover.webp')) }}" alt="">
                </div>
                <input type="file" name="background" accept="image/jpeg,image/png,image/gif,image/webp" data-max-mb="8">
            </div>
            <div class="upload-hint">JPG, PNG, GIF, WebP · {{ __('messages.account.upload_max', ['size' => '8MB']) }}</div>
            <div class="upload-error" hidden></div>
        </div>

        <div class="form-row">
            <label class="form-label">{{ __('messages.account.username') }} <span class="req">*</span></label>
            <input type="text" name="username" value="{{ old('username', $user->username) }}" required>
        </div>
        <div class="form-row">
            <label class="form-label">Email <span class="req">*</span></label>
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
.settings-form .form-label .req { color:#e3342f; font-weight:700; }
.settings-form input[type=text], .settings-form input[type=email], .settings-form input[type=date],
.settings-form select, .settings-form textarea {
    width:100%; max-width:480px; padding:9px 12px; border-radius:5px;
    border:1px solid var(--input-border-color,#d7d7d7);
    background:var(--input-background-color,#fff); color:var(--text-color,#272727); font-size:14px;
}
.settings-form input::placeholder, .settings-form textarea::placeholder { color:var(--meta-color,#999); }
.settings-form .img-upload { display:flex; align-items:center; gap:14px; }
.settings-form .img-preview { width:80px; height:80px; overflow:hidden; border:1px solid var(--border,#2a2a3e); border-radius:8px; flex-shrink:0; }
.settings-form .img-preview.wide { width:160px; height:80px; }
.settings-form .img-preview img { width:100%; height:100%; object-fit:cover; }
.settings-form .upload-hint { font-size:12px; color:var(--meta-color,#999); margin-top:6px; }
.settings-form .upload-error { font-size:13px; color:#e3342f; margin-top:6px; font-weight:500; }
.list-names .btn { text-decoration:none; }
</style>

<script>
(function () {
    var form = document.querySelector('.settings-form');
    if (!form) return;
    var ALLOWED = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    var MSG_TYPE = @json(__('messages.account.upload_invalid_type'));
    var MSG_SIZE = @json(__('messages.account.upload_too_large'));

    form.querySelectorAll('input[type=file]').forEach(function (input) {
        var row = input.closest('.form-row');
        var errBox = row.querySelector('.upload-error');
        var previewImg = row.querySelector('.img-preview img');
        var maxBytes = (parseFloat(input.getAttribute('data-max-mb')) || 4) * 1024 * 1024;

        function showError(msg) {
            errBox.textContent = msg;
            errBox.hidden = false;
            input.value = '';            // bỏ file sai để không submit
            input.setAttribute('data-invalid', '1');
        }
        function clearError() {
            errBox.hidden = true;
            input.removeAttribute('data-invalid');
        }

        input.addEventListener('change', function () {
            clearError();
            var file = input.files && input.files[0];
            if (!file) return;
            // Kiểm tra ĐỊNH DẠNG (báo luôn)
            if (ALLOWED.indexOf(file.type) === -1) {
                showError(MSG_TYPE);
                return;
            }
            // Kiểm tra DUNG LƯỢNG (báo luôn)
            if (file.size > maxBytes) {
                var mb = (file.size / 1024 / 1024).toFixed(1);
                var limit = (maxBytes / 1024 / 1024);
                showError(MSG_SIZE.replace(':size', mb + 'MB').replace(':max', limit + 'MB'));
                return;
            }
            // Hợp lệ -> xem trước ngay
            if (previewImg) previewImg.src = URL.createObjectURL(file);
        });
    });

    // Chặn submit nếu còn file không hợp lệ
    form.addEventListener('submit', function (e) {
        var bad = form.querySelector('input[type=file][data-invalid="1"]');
        if (bad) {
            e.preventDefault();
            bad.focus();
        }
    });
})();
</script>
@endsection
