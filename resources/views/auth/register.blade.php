@extends('layout.novelight')

@section('template_title', 'Đăng ký')

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/account/css/login.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <div class="container-login">
        <div id="login" class="login-form block">
            <h2>Đăng ký</h2>

            @if($errors->any())
                <div class="login-notice" style="color:#f66">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <form method="post" action="{{ route('register') }}">
                @csrf

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-id-card"></i></div>
                    <input type="text" name="name" placeholder="Tên hiển thị" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-user"></i></div>
                    <input type="text" name="username" placeholder="Tên đăng nhập" value="{{ old('username') }}" required>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-envelope"></i></div>
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-key"></i></div>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-lock"></i></div>
                    <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required>
                </div>

                <div class="control-btn">
                    <span></span>
                    <button class="btn btn-primary" type="submit">Đăng ký</button>
                </div>
            </form>

            <hr>
            <div class="alternative">
                <a href="{{ route('auth.google') }}" class="btn btn-google"><i class="fab fa-google"></i><span> Google</span></a>
            </div>
            <p style="font-size:12px;margin-top:10px">Khi đăng ký, bạn đồng ý với
                <a href="{{ route('pages.terms') }}">Điều khoản</a> và
                <a href="{{ route('pages.rules') }}">Nội quy</a>.</p>
        </div>

        <span class="account-span">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></span>
    </div>
</div>
@endsection
