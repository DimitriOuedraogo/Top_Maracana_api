<?php

namespace App\Http\Requests\Competitions; 

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    // 👇 Ajoute cette méthode
    protected function prepareForValidation(): void
    {
        $data = [];

        // Convertit days si c'est une string JSON ex: "[6,0]"
        if ($this->has('days') && is_string($this->days)) {
            $decoded = json_decode($this->days, true);
            $data['days'] = $decoded ?? $this->days;
        }

        // Convertit time_slots si c'est une string JSON
        if ($this->has('time_slots') && is_string($this->time_slots)) {
            $decoded = json_decode($this->time_slots, true);
            $data['time_slots'] = $decoded ?? $this->time_slots;
        }

        // Supprime poster_image si c'est une string vide
        if ($this->has('poster_image') && $this->poster_image === '') {
            $this->request->remove('poster_image');
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'location'          => 'required|string|max:255',
            'start_date'        => 'required|date|after_or_equal:today',
            'end_date'          => 'required|date|after:start_date',
            'max_teams'         => 'required|integer|min:2',
            'players_per_team'  => 'required|integer|min:1',
            'registration_fee'  => 'sometimes|numeric|min:0',
            'prize_description' => 'sometimes|string',
            'age_min'           => 'sometimes|integer|min:0',
            'age_max'           => 'sometimes|integer|gt:age_min',
            'poster_image'      => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'days'              => 'sometimes|array',
            'days.*'            => 'integer|between:0,6',
            'time_slots'        => 'sometimes|array',
            'time_slots.*.start_time' => 'required_with:time_slots|date_format:H:i',
            'time_slots.*.end_time'   => 'required_with:time_slots|date_format:H:i|after:time_slots.*.start_time',
            'matches_per_day' => 'required|integer|in:1,2,3',
        ];
    }
}