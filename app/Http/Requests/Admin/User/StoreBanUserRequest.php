<?php

namespace App\Http\Requests\Admin\User;

class StoreBanUserRequest extends UserBaseRequest
{
    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:255',
            'ban_days' => 'nullable|integer|min:1',
        ];
    }
}
