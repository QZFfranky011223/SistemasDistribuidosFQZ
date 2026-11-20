<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'game',
        'zone_or_community',
        'format',
        'min_squad_members',
        'max_teams',
        'prize_pool',
        'start_date',
        'status',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function squads(): BelongsToMany
    {
        return $this->belongsToMany(Squad::class, 'tournament_squad')
            ->withPivot(['registered_by', 'seed_number', 'registered_at'])
            ->withTimestamps();
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'tournament_id')->orderBy('match_number');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Abierto a inscripciones',
            'in_progress' => 'En curso',
            'finished' => 'Finalizado',
            default => ucfirst($this->status),
        };
    }

    public function isFull(): bool
    {
        return $this->squads()->count() >= $this->max_teams;
    }
}
