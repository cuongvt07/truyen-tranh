@extends('layout.client')

@section('template_title')
    {{ __('Đăng nhập') }}
@endsection

@section('content')
<form action="{{ route('login.post') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-sm-12">
            <div id="login-signup-form">
                <div id="ctl00_mainContent_pnlStandardLogin">
                    <div class="row">
                        <div class="col-sm-offset-3 col-sm-6">
                            <div class="user-page clearfix">
                                {{-- Login: email hoặc username --}}
                                <div class="form-group">
                                    <label for="login">Email hoặc tên đăng nhập</label>
                                    <input type="text" class="form-control" id="login" name="login"
                                           value="{{ old('login') }}" placeholder="Email hoặc Tên đăng nhập" required>
                                    @error('login')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="form-group">
                                    <label for="password">Mật khẩu</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Remember me --}}
                                <div class="form-group">
                                    <input type="checkbox" id="remember" name="remember" value="true" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="remember">Ghi nhớ</label>
                                </div>

                                {{-- Actions --}}
                                <div class="login-action">
                                    <div class="form-group">
                                        <a id="user-password-recovery" class="login-link" href="{{ route('password.request') }}">Quên mật khẩu</a>
                                        <a id="user-signup-link" class="login-link" href="{{ route('register') }}">Đăng ký mới</a>
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" value="Đăng nhập" id="user-login" tabindex="10" class="btn btn-primary">
                                    </div>
                                </div>

                                {{-- Nếu muốn hiện lỗi chung --}}
                                @if ($errors->has('error'))
                                    <div class="alert alert-danger">{{ $errors->first('error') }}</div>
                                @endif
                            </div>

                            {{-- Social login (tuỳ chọn) --}}
                            {{-- ... --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection
