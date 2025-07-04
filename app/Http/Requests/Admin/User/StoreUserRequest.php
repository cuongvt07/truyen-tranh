<?php

namespace App\Http\Requests\Admin\User;

class StoreUserRequest extends UserBaseRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:0,1,2', // 0: user, 1: poster, 2: admin
        ];
    }
}
