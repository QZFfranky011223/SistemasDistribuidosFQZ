<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Squad extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag',
        'logo_color',
        'primary_game',
        'region',
        'description',
        'discord_invite',
        'leader_id',
        'invite_code',
        'max_members',
        'is_recruiting',
        'wins',
    ];

    protected function casts(): array
    {
        return [
            'is_recruiting' => 'boolean',
        ];
    }

    public static function generateInviteCode(): string
    {
        do {
            $code = 'LM-' . strtoupper(Str::random(6));
        } while (self::where('invite_code', $code)->exists());

        return $code;
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(SquadMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'squad_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function activeMembers(): BelongsToMany
    {
        return $this->members()->wherePivot('status', 'active');
    }

    public function pendingMembers(): BelongsToMany
    {
        return $this->members()->wherePivot('status', 'pending');
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_squad')
            ->withPivot(['registered_by', 'seed_number', 'registered_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SquadMessage::class)->latest();
    }
}
