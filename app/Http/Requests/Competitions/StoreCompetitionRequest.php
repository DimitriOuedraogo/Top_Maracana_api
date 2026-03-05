<?php

namespace App\Http\Requests\Competitions; 

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

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