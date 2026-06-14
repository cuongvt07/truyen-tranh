<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            // 'name' (Full name) là tuỳ chọn — cột DB nullable, không bắt buộc trên form.
            'name' => ['nullable', 'string', 'max:255'],
            // Sometimes: form có thể không gửi đủ field; nếu có thì phải hợp lệ + không trùng.
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique(User::class, 'username')->ignore($userId)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($userId)],
            'gender' => ['nullable'],
            'date_of_birth' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            // BẢO MẬT upload: chỉ chấp nhận đúng ảnh (chặn .php/.svg đội lốt), giới hạn dung lượng.
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
            'background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:8192'],
        ];
    }
}
