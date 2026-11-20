<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\GamerProfile;
use App\Models\Squad;
use App\Models\SquadMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LiveArenaController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user()->load('gamerProfile');
        $activeSquad = $user->activeSquad();

        $squads = Squad::with(['leader.gamerProfile', 'activeMembers.gamerProfile'])
            ->withCount('activeMembers')
            ->orderByDesc('wins')
            ->get();

        $leaderboard = GamerProfile::with('user')
            ->orderByDesc('level')
            ->orderByDesc('wins')
            ->take(8)
            ->get();

        return view('arena.index', compact('user', 'activeSquad', 'squads', 'leaderboard'));
    }

    public function liveFeed(Request $request): JsonResponse
    {
        if ($request->boolean('simulate')) {
            $this->simulateRandomSquadActivity();
        }

        $squads = Squad::with(['leader.gamerProfile', 'activeMembers.gamerProfile'])
            ->withCount('activeMembers')
            ->orderByDesc('wins')
            ->get()
            ->map(function (Squad $squad) {
                $lastMsg = SquadMessage::with('user.gamerProfile')
                    ->where('squad_id', $squad->id)
                    ->latest()
                    ->first();

                $trend = $squad->wins >= 10 ? 'improving' : ($squad->wins >= 8 ? 'stable' : 'warning');

                return [
                    'id' => $squad->id,
                    'name' => $squad->name,
                    'tag' => $squad->tag,
                    'logo_color' => $squad->logo_color,
                    'primary_game' => $squad->primary_game,
                    'wins' => $squad->wins,
                    'members_count' => $squad->active_members_count,
                    'max_members' => $squad->max_members,
                    'trend' => $trend,
                    'live_action' => $lastMsg ? $lastMsg->message : 'Entrenando en el Lobby',
                    'actor' => $lastMsg ? ($lastMsg->user->gamerProfile->gamertag ?? $lastMsg->user->name) : ($squad->leader->gamerProfile->gamertag ?? 'Capitán'),
                    'updated_ago' => $lastMsg ? $lastMsg->created_at->diffForHumans() : 'En línea',
                ];
            });

        $recentEvents = SquadMessage::with(['squad', 'user.gamerProfile'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (SquadMessage $msg) => [
                'id' => $msg->id,
                'squad_tag' => $msg->squad->tag ?? 'SOLO',
                'squad_name' => $msg->squad->name ?? 'Jugador',
                'squad_color' => $msg->squad->logo_color ?? '#8b5cf6',
                'gamertag' => $msg->user->gamerProfile->gamertag ?? $msg->user->name,
                'message' => $msg->message,
                'time' => $msg->created_at->format('H:i:s'),
            ]);

        $liveMatches = GameMatch::with(['tournament', 'squadA', 'squadB'])
            ->whereIn('status', ['live', 'scheduled'])
            ->take(4)
            ->get()
            ->map(fn (GameMatch $m) => [
                'id' => $m->id,
                'tournament' => $m->tournament->name ?? 'Torneo',
                'round' => $m->round_name,
                'squad_a' => $m->squadA->tag ?? 'TBD',
                'squad_b' => $m->squadB->tag ?? 'TBD',
                'score_a' => $m->score_a,
                'score_b' => $m->score_b,
                'status' => $m->status_label,
            ]);

        $leaderboard = GamerProfile::orderByDesc('level')
            ->orderByDesc('wins')
            ->take(6)
            ->get(['gamertag', 'favorite_game', 'rank_tier', 'level', 'wins', 'matches_played', 'reputation_score']);

        return response()->json([
            'timestamp' => now()->format('H:i:s'),
            'squads' => $squads,
            'events' => $recentEvents,
            'matches' => $liveMatches,
            'leaderboard' => $leaderboard,
        ]);
    }

    public function submitResult(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $profile = $user->gamerProfile;

        $validated = $request->validate([
            'mode' => ['required', 'string', 'max:60'],
            'score' => ['required', 'integer', 'min:0', 'max:1000'],
            'accuracy' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $isImproving = $validated['accuracy'] >= 60;
        $levelDelta = $isImproving ? max(1, (int) round($validated['score'] / 40)) : -1;

        $oldLevel = $profile->level;
        $newLevel = max(1, $oldLevel + $levelDelta);

        $profile->matches_played += 1;
        if ($isImproving) {
            $profile->wins += 1;
        }
        $profile->level = $newLevel;

        $profile->rank_tier = match (true) {
            $newLevel >= 80 => 'Unreal / Radiante',
            $newLevel >= 60 => 'Inmortal',
            $newLevel >= 45 => 'Ascendente',
            $newLevel >= 30 => 'Diamante II',
            $newLevel >= 20 => 'Platino',
            $newLevel >= 10 => 'Oro I',
            default => 'Plata',
        };
        $profile->save();

        $squad = $user->activeSquad() ?? Squad::first();
        $modeLabel = match ($validated['mode']) {
            'code_royale' => 'Reto de Programación CodeRoyale',
            'aim_reflex' => 'Test de Reflejos',
            default => 'Simulación Táctica',
        };

        if ($squad) {
            if ($isImproving) {
                $squad->increment('wins');
            }

            $statusIcon = $isImproving ? '📈 MEJORANDO' : '📉 EN CAÍDA';
            SquadMessage::create([
                'squad_id' => $squad->id,
                'user_id' => $user->id,
                'message' => "[{$statusIcon}] {$profile->gamertag} completó '{$modeLabel}' con {$validated['score']} pts ({$validated['accuracy']}%). Nivel: {$newLevel} ({$profile->rank_tier}).",
                'is_pinned' => false,
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'is_improving' => $isImproving,
            'trend_text' => $isImproving
                ? "📈 Subiste a Nivel {$newLevel} ({$profile->rank_tier})"
                : "📉 Rendimiento bajo ({$validated['accuracy']}%). Nivel {$newLevel}.",
            'level' => $newLevel,
            'level_delta' => $levelDelta,
            'rank_tier' => $profile->rank_tier,
            'wins' => $profile->wins,
            'matches_played' => $profile->matches_played,
            'win_rate' => $profile->win_rate,
        ]);
    }

    private function simulateRandomSquadActivity(): void
    {
        $squad = Squad::inRandomOrder()->first();
        if (! $squad) {
            return;
        }

        $member = $squad->activeMembers()->inRandomOrder()->first() ?? $squad->leader;
        $gamertag = $member->gamerProfile->gamertag ?? $member->name;

        $actions = [
            "📈 {$gamertag} resolvió el desafío de programación en 18 segundos (+25 pts para [{$squad->tag}]).",
            "⚔️ El Squad [{$squad->tag}] entró a sala de práctica personalizada.",
            "🎯 {$gamertag} logró 94% de precisión en el Test de Reflejos.",
            "📉 [{$squad->tag}] cayó en la ronda de práctica y ajusta su alineación.",
            "🏆 [{$squad->tag}] suma una nueva victoria en la Arena.",
        ];

        $randomAction = $actions[array_rand($actions)];

        if (str_contains($randomAction, '📈') || str_contains($randomAction, '🏆')) {
            $squad->increment('wins');
        }

        SquadMessage::create([
            'squad_id' => $squad->id,
            'user_id' => $member->id,
            'message' => $randomAction,
            'is_pinned' => false,
        ]);
    }
}
