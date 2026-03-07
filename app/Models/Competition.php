<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Group;
use App\Models\GameMatch;


class Competition extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'organizer_id',
        'name',
        'location',
        'start_date',
        'end_date',
        'max_teams',
        'players_per_team',
        'registration_fee',
        'prize_description',
        'age_min',
        'age_max',
        'poster_image',
        'status',
        'is_verified',
        'matches_per_day',
        'winner_id',
        'top_scorer_id',
        'best_player_id',
        'best_goalkeeper_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_fee' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    protected $hidden = [
        'is_verified',
        'created_at',
        'updated_at',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(CompetitionDay::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(CompetitionTimeSlot::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function topScorer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'top_scorer_id');
    }

    public function bestPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'best_player_id');
    }

    public function bestGoalkeeper(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'best_goalkeeper_id');
    }

}