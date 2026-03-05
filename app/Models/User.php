<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\HasOne;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'email_verification_code',
        'email_verification_expires_at',
        'password_reset_code',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'email_verification_code',
        'password_reset_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'password_reset_expires_at' => 'datetime',
    ];

    // -----------------------------------------------
    // JWT required methods
    // -----------------------------------------------

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
        ];
    }

    // -----------------------------------------------
    // Helper methods
    // -----------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOrganizer(): bool
    {
        return $this->role === 'organizer';
    }

    public function isTeamManager(): bool
    {
        return $this->role === 'team_manager';
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function team(): HasOne
    {
        return $this->hasOne(Team::class, 'manager_id');
    }
}