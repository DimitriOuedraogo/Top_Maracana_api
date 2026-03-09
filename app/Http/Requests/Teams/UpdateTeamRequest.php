<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('players') && is_string($this->players)) {
            $decoded = json_decode($this->players, true);
            $this->merge(['players' => $decoded ?? $this->players]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'players' => 'sometimes|array',
            'players.*.full_name' => 'required_with:players|string|max:255',
            'players.*.national_id_number' => 'sometimes|string|unique:players,national_id_number',
            'players.*.national_id_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'players.*.birth_date' => 'required_with:players|date',
        ];
    }
}