<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        if (!config('services.google.client_id')) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Đăng nhập Google chưa được cấu hình. Vui lòng liên hệ quản trị viên.']);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Xử lý callback từ Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Đăng nhập Google thất bại, vui lòng thử lại.']);
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

            // Thưởng 30 xu cho tài khoản MỚI đăng ký bằng Google (đăng ký thường không có).
            // points không nằm trong $fillable nên set tường minh ở đây.
            $user->increment('points', 30);
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
