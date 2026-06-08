@extends('layout.novelight')

@section('template_title', 'Đăng nhập')

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/account/css/login.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <div class="container-login">
        <div id="login" class="login-form block">
            <h2>Đăng nhập</h2>

            @if($errors->any())
                <div class="login-notice" style="color:#f66">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <form method="post" action="{{ route('login') }}">
                @csrf

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-user"></i></div>
                    <input type="text" name="login" placeholder="Email hoặc tên đăng nhập"
                           value="{{ old('login') }}" required autofocus>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-key"></i></div>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                </div>

                <div class="control-btn">
                    <a class="forgot-password-link" href="{{ route('password.request') }}">Quên mật khẩu?</a>
                    <button class="btn btn-primary" type="submit">Đăng nhập</button>
                </div>
            </form>

            <hr>
            <div class="alternative">
                <a href="{{ route('auth.google') }}" class="btn btn-google"><i class="fab fa-google"></i><span> Google</span></a>
            </div>
        </div>

        <span class="account-span">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></span>
    </div>
</div>
@endsection
