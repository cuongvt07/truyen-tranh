<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $login    = $request->input('login');
        $password = $request->input('password');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $login)->first();

        if ($user && Hash::check($password, $user->password)) {
            \Log::info('✅ Basic login success', [
                'user_id' => $user->id,
                'user'    => $user->email,
            ]);

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        \Log::warning('❌ Basic login failed', ['login' => $login]);
        throw ValidationException::withMessages([
            'login' => 'Email hoặc tên đăng nhập / mật khẩu không chính xác.',
        ]);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect('/');
        } catch (Exception $e) {
            // Handle the exception (e.g., log it or display an error message)
            return redirect('/')->withErrors(['error' => 'Failed to log out.']);
        }
    }
}
