<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyResetCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'email est obligatoire.',
            'email.email'    => 'Format d\'email invalide.',
            'code.required'  => 'Le code est obligatoire.',
            'code.size'      => 'Le code doit contenir exactement 6 caractères.',
        ];
    }
}