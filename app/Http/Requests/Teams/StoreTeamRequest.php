<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'competition_id'               => 'required|uuid|exists:competitions,id',
            'name'                          => 'required|string|max:255',
            'logo'                          => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'players'                       => 'sometimes|array',
            'players.*.full_name'           => 'required_with:players|string|max:255',
            'players.*.national_id_number'  => 'sometimes|string|unique:players,national_id_number',
            'players.*.national_id_photo'   => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'players.*.birth_date'          => 'required_with:players|date',
        ];
    }
}