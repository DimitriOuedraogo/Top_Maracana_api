<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class CompetitionTimeSlot extends Model
{
     use HasUuids;                    // ← génère l'UUID automatiquement
    
    protected $keyType = 'string';   // ← dit à Laravel que l'ID est une string
    public $incrementing = false;  
    protected $fillable = ['competition_id', 'start_time', 'end_time'];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}