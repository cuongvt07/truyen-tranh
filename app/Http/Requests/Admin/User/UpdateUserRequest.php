<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends UserBaseRequest
{
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user ? $user->id : null;

        return [
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:0,1,2',
            'points' => 'nullable|integer|min:0',
        ];
    }
}
