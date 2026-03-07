<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competition_id' => 'required|uuid|exists:competitions,id',
            'name' => 'required|string|max:255',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'players' => 'sometimes|array',
            'players.*.full_name' => 'required_with:players|string|max:255',
            'players.*.national_id_number' => 'sometimes|string|unique:players,national_id_number',
            'players.*.national_id_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'players.*.birth_date' => 'required_with:players|date',
            'players.*.is_goalkeeper' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            // Competition
            'competition_id.required' => 'La compétition est obligatoire.',
            'competition_id.uuid' => 'L\'identifiant de la compétition est invalide.',
            'competition_id.exists' => 'La compétition sélectionnée n\'existe pas.',

            // Équipe
            'name.required' => 'Le nom de l\'équipe est obligatoire.',
            'name.string' => 'Le nom de l\'équipe doit être une chaîne de caractères.',
            'name.max' => 'Le nom de l\'équipe ne doit pas dépasser 255 caractères.',

            // Logo
            'logo.image' => 'Le logo doit être une image.',
            'logo.mimes' => 'Le logo doit être au format jpeg, png ou jpg.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',

            // Joueurs
            'players.array' => 'Les joueurs doivent être une liste.',

            // Nom du joueur
            'players.*.full_name.required_with' => 'Le nom complet du joueur est obligatoire.',
            'players.*.full_name.string' => 'Le nom complet du joueur doit être une chaîne de caractères.',
            'players.*.full_name.max' => 'Le nom complet du joueur ne doit pas dépasser 255 caractères.',

            // Numéro CNI
            'players.*.national_id_number.string' => 'Le numéro de CNI doit être une chaîne de caractères.',
            'players.*.national_id_number.unique' => 'Ce numéro de CNI est déjà utilisé.',

            // Photo CNI
            'players.*.national_id_photo.image' => 'La photo de CNI doit être une image.',
            'players.*.national_id_photo.mimes' => 'La photo de CNI doit être au format jpeg, png ou jpg.',
            'players.*.national_id_photo.max' => 'La photo de CNI ne doit pas dépasser 2 Mo.',

            // Date de naissance
            'players.*.birth_date.required_with' => 'La date de naissance du joueur est obligatoire.',
            'players.*.birth_date.date' => 'La date de naissance du joueur est invalide.',

            // Gardien
            'players.*.is_goalkeeper.required' => 'Vous devez préciser si le joueur est gardien ou non.',
            'players.*.is_goalkeeper.boolean' => 'Le champ gardien doit être vrai ou faux.',
        ];
    }
}