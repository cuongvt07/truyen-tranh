<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Gender;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegsiterRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegsiterRequest $request): RedirectResponse
    {
        $user = User::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => '/images/users/default.jpg',
            'gender' => Gender::MALE,
            'description' => '',
            'role' => UserRole::USER,
            'email_verified_at' => now(),
        ]);

        // event(new Registered($user));

        // Thưởng coin đăng ký thường (cấu hình admin 'signup_bonus', mặc định 30, 0 = tắt). Ghi ledger + thông báo.
        $bonus = max(0, (int) setting('signup_bonus', 30));
        if ($bonus > 0) {
            \App\Services\CreditService::adjust($user->id, $bonus, 'signup_bonus');
            $user->notify(new \App\Notifications\NewUserGiftNotification($bonus));
        }

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
