<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gamertag',
        'favorite_game',
        'platform',
        'game_id',
        'rank_tier',
        'level',
        'discord_handle',
        'stream_url',
        'bio',
        'wins',
        'matches_played',
        'reputation_score',
    ];

    protected function casts(): array
    {
        return [
            'reputation_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWinRateAttribute(): int
    {
        if ($this->matches_played <= 0) {
            return 0;
        }

        return (int) round(($this->wins / $this->matches_played) * 100);
    }
}
