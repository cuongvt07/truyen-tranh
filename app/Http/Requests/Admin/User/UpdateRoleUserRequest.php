<?php

namespace App\Http\Requests\Admin\User;

class UpdateRoleUserRequest extends UserBaseRequest
{
    public function rules(): array
    {
        return [
            'role' => 'required|in:0,1,2',
        ];
    }
}
