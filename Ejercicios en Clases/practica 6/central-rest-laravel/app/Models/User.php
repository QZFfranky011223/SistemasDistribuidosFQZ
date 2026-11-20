<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function gamerProfile(): HasOne
    {
        return $this->hasOne(GamerProfile::class);
    }

    public function ledSquads(): HasMany
    {
        return $this->hasMany(Squad::class, 'leader_id');
    }

    public function squadMemberships(): HasMany
    {
        return $this->hasMany(SquadMember::class);
    }

    public function squads(): BelongsToMany
    {
        return $this->belongsToMany(Squad::class, 'squad_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function activeSquad(): ?Squad
    {
        return $this->squads()
            ->wherePivot('status', 'active')
            ->first();
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(PlayerReview::class, 'reviewed_user_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(PlayerReview::class, 'reviewer_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCaptain(): bool
    {
        return $this->role === 'captain' || $this->squadMemberships()
            ->where('role', 'captain')
            ->where('status', 'active')
            ->exists();
    }
}
