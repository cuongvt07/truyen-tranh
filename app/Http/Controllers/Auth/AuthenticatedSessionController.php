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
        try {
            $request->authenticate();
            $request->session()->regenerate();

            return redirect()->intended(RouteServiceProvider::HOME);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('Login error: '.$e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra, vui lòng thử lại sau.']);
        }
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
