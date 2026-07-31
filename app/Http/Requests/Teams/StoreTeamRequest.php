<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
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
            'name'                             => 'required|string|max:255',
            'logo'                             => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'players'                          => 'required|array|min:1',
            'players.*.full_name'              => 'required|string|max:255',
            'players.*.birth_date'             => 'required|date',
            'players.*.is_goalkeeper'          => 'required|boolean',
            'players.*.national_id_number'     => 'sometimes|string|unique:players,national_id_number',
            'players.*.national_id_photo'      => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                        => 'Le nom de l\'équipe est obligatoire.',
            'name.max'                             => 'Le nom de l\'équipe ne doit pas dépasser 255 caractères.',
            'logo.image'                           => 'Le logo doit être une image.',
            'logo.mimes'                           => 'Le logo doit être au format jpeg, png ou jpg.',
            'logo.max'                             => 'Le logo ne doit pas dépasser 2 Mo.',
            'players.required'                     => 'Vous devez ajouter au moins un joueur.',
            'players.array'                        => 'Les joueurs doivent être une liste.',
            'players.min'                          => 'Vous devez ajouter au moins un joueur.',
            'players.*.full_name.required'         => 'Le nom complet du joueur est obligatoire.',
            'players.*.birth_date.required'        => 'La date de naissance du joueur est obligatoire.',
            'players.*.birth_date.date'            => 'La date de naissance du joueur est invalide.',
            'players.*.is_goalkeeper.required'     => 'Vous devez préciser si le joueur est gardien ou non.',
            'players.*.is_goalkeeper.boolean'      => 'Le champ gardien doit être vrai ou faux.',
            'players.*.national_id_number.unique'  => 'Ce numéro de CNI est déjà utilisé.',
        ];
    }
}
