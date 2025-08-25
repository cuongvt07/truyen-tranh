<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $login = $this->input('login');
        $password = $this->input('password');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field     => $login,
            'password' => $password,
        ];

        \Log::info('🔑 Basic Login attempt', [
            'field' => $field,
            'login' => $login,
        ]);

        if (! Auth::attempt($credentials)) {
            \Log::warning('❌ Login failed', ['login' => $login]);
            throw ValidationException::withMessages([
                'login' => 'Email hoặc tên đăng nhập / mật khẩu không chính xác.',
            ]);
        }

        \Log::info('✅ Login success', ['user_id' => Auth::id()]);
    }
}
