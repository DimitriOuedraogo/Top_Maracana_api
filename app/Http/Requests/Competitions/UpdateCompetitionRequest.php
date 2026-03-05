<?php

namespace App\Http\Requests\Competitions; 

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => 'sometimes|string|max:255',
            'location'          => 'sometimes|string|max:255',
            'start_date'        => 'sometimes|date',
            'end_date'          => 'sometimes|date|after:start_date',
            'max_teams'         => 'sometimes|integer|min:2',
            'players_per_team'  => 'sometimes|integer|min:1',
            'registration_fee'  => 'sometimes|numeric|min:0',
            'prize_description' => 'sometimes|string',
            'age_min'           => 'sometimes|integer|min:0',
            'age_max'           => 'sometimes|integer|gt:age_min',
            'poster_image'      => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'status'            => 'sometimes|in:registration_open,full,ongoing,finished',
            'days'              => 'sometimes|array',
            'days.*'            => 'integer|between:0,6',
            'time_slots'        => 'sometimes|array',
            'time_slots.*.start_time' => 'required_with:time_slots|date_format:H:i',
            'time_slots.*.end_time'   => 'required_with:time_slots|date_format:H:i',
            'matches_per_day' => 'required|integer|in:1,2,3',
        ];
    }
}