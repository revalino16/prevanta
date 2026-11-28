<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Auth\VerifyPasswordOtpRequest;

class VerifyPasswordOtpApiRequest extends VerifyPasswordOtpRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => ['bail', 'required', 'string', 'email', 'max:100'],
        ];
    }
}
