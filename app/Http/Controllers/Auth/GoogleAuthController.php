<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Chuyển hướng sang Google để đăng nhập.
     */
    public function redirect()
    {
        $this->configureGoogleFromDb();

        if (!config('services.google.client_id')) {
            return redirect()->route('login')
                ->withErrors(['login' => __('messages.auth.google_not_configured')]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Ưu tiên Client ID/Secret từ DB (admin Payment Settings); thiếu thì giữ .env.
     * Override config runtime để Socialite dùng giá trị DB.
     */
    private function configureGoogleFromDb(): void
    {
        try {
            $ps = PaymentSetting::current();
            if (filled($ps->google_client_id)) {
                config(['services.google.client_id' => $ps->google_client_id]);
            }
            if (filled($ps->google_client_secret)) {
                config(['services.google.client_secret' => $ps->google_client_secret]);
            }
        } catch (Throwable $e) {
            // Lỗi đọc/giải mã (vd APP_KEY đổi) → giữ nguyên .env.
        }
    }

    /**
     * Xử lý callback từ Google.
     */
    public function callback()
    {
        $this->configureGoogleFromDb();

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['login' => __('messages.auth.google_failed')]);
        }

        // 1. Đã liên kết google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        // 2. Hoặc khớp email -> liên kết
        if (!$user && $googleUser->getEmail()) {
            $user = User::where('email', $googleUser->getEmail())->first();
            if ($user) {
                $user->google_id = $googleUser->getId();
                if (empty($user->avatar) && $googleUser->getAvatar()) {
                    $user->avatar = $googleUser->getAvatar();
                }
                $user->save();
            }
        }

        // 3. Tạo mới
        if (!$user) {
            $user = User::create([
                'name'        => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Người dùng'),
                'username'    => $this->uniqueUsername($googleUser->getEmail(), $googleUser->getId()),
                'email'       => $googleUser->getEmail(),
                'google_id'   => $googleUser->getId(),
                'avatar'      => $googleUser->getAvatar() ?: '/images/users/default.jpg',
                'description' => '',
                'password'    => Hash::make(Str::random(32)),
                'role'        => 0,
                'email_verified_at' => now(),
            ]);

            // Thưởng xu cho tài khoản MỚI đăng ký bằng Google (đăng ký thường không có).
            // Số xu cấu hình ở admin settings 'google_signup_bonus' (mặc định 30, 0 = tắt).
            // points không nằm trong $fillable nên set tường minh ở đây.
            $bonus = max(0, (int) setting('google_signup_bonus', 30));
            if ($bonus > 0) {
                \App\Services\CreditService::adjust($user->id, $bonus, 'signup_bonus');
            }
        }

        Auth::login($user, true);

        return redirect()->intended(route('home.index'));
    }

    /**
     * Sinh username duy nhất (<=20 ký tự) từ email.
     */
    private function uniqueUsername(?string $email, string $googleId): string
    {
        $base = $email ? Str::before($email, '@') : 'user';
        $base = Str::slug($base, '');            // bỏ ký tự đặc biệt
        $base = Str::substr($base, 0, 14) ?: 'user';

        $username = $base;
        $i = 0;
        while (User::where('username', $username)->exists()) {
            $i++;
            $username = Str::substr($base, 0, 14) . $i;
            if ($i > 9999) {
                $username = 'gg' . Str::substr($googleId, -10);
                break;
            }
        }

        return Str::substr($username, 0, 20);
    }
}
