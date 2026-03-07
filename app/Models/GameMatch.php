<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'matches'; // ← mot réservé PHP

    protected $fillable = [
        'competition_id',
        'group_id',
        'home_team_id',
        'away_team_id',
        'week_number',
        'day_of_week',
        'match_time',
        'round_type',
        'status',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }


    public function result(): HasOne
    {
        return $this->hasOne(MatchResult::class, 'match_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(MatchCard::class, 'match_id');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(MatchGoal::class, 'match_id');
    }
}