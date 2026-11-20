<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'round_name',
        'match_number',
        'squad_a_id',
        'squad_b_id',
        'score_a',
        'score_b',
        'winner_squad_id',
        'scheduled_at',
        'status',
        'lobby_code',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function squadA(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'squad_a_id');
    }

    public function squadB(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'squad_b_id');
    }

    public function winnerSquad(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'winner_squad_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PlayerReview::class, 'match_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Programada',
            'live' => 'En vivo',
            'finished' => 'Finalizada',
            default => ucfirst($this->status),
        };
    }
}
