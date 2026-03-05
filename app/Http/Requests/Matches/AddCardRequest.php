<?php

namespace App\Http\Requests\Matches;

use Illuminate\Foundation\Http\FormRequest;

class AddCardRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'player_id' => 'required|uuid|exists:players,id',
            'card_type' => 'required|in:yellow,red',
            'minute'    => 'required|integer|min:1|max:120',
        ];
    }
}