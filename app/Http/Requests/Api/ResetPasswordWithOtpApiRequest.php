<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Auth\ResetPasswordWithOtpRequest;

class ResetPasswordWithOtpApiRequest extends ResetPasswordWithOtpRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => ['bail', 'required', 'string', 'email', 'max:100'],
            'reset_token' => ['bail', 'required', 'string', 'size:64'],
        ];
    }
}
