<?php

namespace App\Http\Requests\Competitions;

use Illuminate\Foundation\Http\FormRequest;

class UploadPosterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'poster_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
