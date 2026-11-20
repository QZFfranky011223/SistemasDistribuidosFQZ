<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\GamerProfile;
use App\Models\PlayerReview;
use App\Models\Squad;
use App\Models\SquadMessage;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function welcome(): View
    {
        $featuredTournaments = Tournament::withCount('squads')
            ->orderByRaw("CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 ELSE 3 END")
            ->orderBy('start_date')
            ->take(6)
            ->get();

        $topSquads = Squad::withCount('activeMembers')
            ->orderByDesc('wins')
            ->take(4)
            ->get();

        $liveMatches = GameMatch::with(['tournament', 'squadA', 'squadB', 'winnerSquad'])
            ->whereIn('status', ['live', 'scheduled', 'finished'])
            ->latest('scheduled_at')
            ->take(4)
            ->get();

        $stats = [
            'players' => User::count(),
            'squads' => Squad::count(),
            'tournaments' => Tournament::count(),
            'matches' => GameMatch::count(),
        ];

        return view('welcome', compact('featuredTournaments', 'topSquads', 'liveMatches', 'stats'));
    }

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user()->load([
            'gamerProfile',
            'reviewsReceived.reviewer.gamerProfile',
        ]);

        if (! $user->gamerProfile) {
            GamerProfile::create([
                'user_id' => $user->id,
                'gamertag' => $user->name,
                'favorite_game' => 'Fortnite',
                'platform' => 'PC',
                'game_id' => 'EPIC-' . strtoupper(substr(md5($user->email), 0, 6)),
                'rank_tier' => 'Oro I',
                'level' => 1,
                'reputation_score' => 5.00,
            ]);
            $user->load('gamerProfile');
        }

        $activeSquad = $user->squads()
            ->wherePivot('status', 'active')
            ->with([
                'leader.gamerProfile',
                'activeMembers.gamerProfile',
                'pendingMembers.gamerProfile',
                'tournaments' => fn ($q) => $q->withCount('squads')->orderBy('start_date'),
                'messages.user.gamerProfile',
            ])
            ->first();

        $myTournaments = $activeSquad
            ? $activeSquad->tournaments
            : collect();

        $myMatches = $activeSquad
            ? GameMatch::with(['tournament', 'squadA', 'squadB', 'winnerSquad'])
                ->where(function ($query) use ($activeSquad) {
                    $query->where('squad_a_id', $activeSquad->id)
                        ->orWhere('squad_b_id', $activeSquad->id);
                })
                ->orderByRaw("CASE status WHEN 'live' THEN 1 WHEN 'scheduled' THEN 2 ELSE 3 END")
                ->orderBy('scheduled_at')
                ->get()
            : collect();

        $openTournaments = Tournament::withCount('squads')
            ->where('status', 'open')
            ->orderBy('start_date')
            ->take(4)
            ->get();

        $allSquads = Squad::withCount('activeMembers')->orderByDesc('wins')->get();
        $onlineUsers = User::with('gamerProfile')->take(8)->get();

        $platformStats = [
            'total_users' => User::count(),
            'total_squads' => Squad::count(),
            'open_tournaments' => Tournament::where('status', 'open')->count(),
            'live_tournaments' => Tournament::where('status', 'in_progress')->count(),
            'finished_tournaments' => Tournament::where('status', 'finished')->count(),
            'total_reviews' => PlayerReview::count(),
            'tournaments_by_game' => Tournament::selectRaw('game, count(*) as total')
                ->groupBy('game')
                ->pluck('total', 'game'),
        ];

        return view('dashboard', compact(
            'user',
            'activeSquad',
            'myTournaments',
            'myMatches',
            'openTournaments',
            'allSquads',
            'onlineUsers',
            'platformStats'
        ));
    }

    public function updateLiveGameState(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user()->load('gamerProfile');
        $squad = $user->activeSquad();

        $payload = [
            'is_live' => (bool) $request->input('is_live', true),
            'player_id' => $user->id,
            'gamertag' => $user->gamerProfile->gamertag ?? $user->name,
            'squad_tag' => $squad ? $squad->tag : 'SOLO',
            'squad_name' => $squad ? $squad->name : ($user->gamerProfile->gamertag ?? $user->name),
            'game' => $request->input('game', $user->gamerProfile->favorite_game ?? 'Valorant'),
            'score' => (int) $request->input('score', 0),
            'rival_score' => (int) $request->input('rival_score', 0),
            'kills' => (int) $request->input('kills', 0),
            'crystals_count' => (int) $request->input('crystals_count', 0),
            'hp' => (int) $request->input('hp', 100),
            'elapsed_seconds' => (int) $request->input('elapsed_seconds', 0),
            'remaining_seconds' => (int) $request->input('remaining_seconds', 300),
            'total_seconds' => 300,
            'progress_pct' => (int) $request->input('progress_pct', 0),
            'player_x' => (float) $request->input('player_x', 360),
            'player_y' => (float) $request->input('player_y', 160),
            'crystals' => $request->input('crystals', []),
            'enemies' => $request->input('enemies', []),
            'bullets' => $request->input('bullets', []),
            'last_event' => (string) $request->input('last_event', 'Jugando partida en vivo...'),
            'updated_at' => now()->timestamp,
            'updated_time' => now()->format('H:i:s'),
        ];

        $filePath = storage_path('app/live_stream.json');
        file_put_contents($filePath, json_encode($payload));

        return response()->json(['ok' => true]);
    }

    public function getLiveGameState(): JsonResponse
    {
        $filePath = storage_path('app/live_stream.json');
        if (! file_exists($filePath)) {
            return response()->json(['is_live' => false]);
        }

        $data = json_decode((string) file_get_contents($filePath), true);
        if (! is_array($data)) {
            return response()->json(['is_live' => false]);
        }

        $ageSeconds = now()->timestamp - ($data['updated_at'] ?? 0);
        if ($ageSeconds > 20) {
            $data['is_live'] = false;
        }

        return response()->json($data);
    }

    public function startQuickTournament(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $game = $request->input('game', 'Valorant');
        $squads = Squad::with('leader.gamerProfile')->take(4)->get();

        while ($squads->count() < 4) {
            $idx = $squads->count() + 1;
            $newSquad = Squad::create([
                'name' => 'Escuadra Alfa ' . $idx . ' ' . Str::upper(Str::random(2)),
                'tag' => 'SQ' . $idx . rand(1, 9),
                'logo_color' => ['#8b5cf6', '#06b6d4', '#10b981', '#f59e0b'][$idx % 4],
                'primary_game' => $game,
                'region' => 'Sucre / USFX',
                'leader_id' => $user->id,
                'invite_code' => Squad::generateInviteCode(),
                'max_members' => 5,
                'wins' => rand(2, 8),
            ]);
            $squads->push($newSquad);
        }

        $s1 = $squads[0];
        $s2 = $squads[1];
        $s3 = $squads[2];
        $s4 = $squads[3];

        $tournament = Tournament::create([
            'name' => 'Copa Relámpago ' . $game . ' #' . rand(10, 99),
            'game' => $game,
            'zone_or_community' => 'USFX / Sucre',
            'format' => 'Eliminación Directa',
            'min_squad_members' => 2,
            'max_teams' => 4,
            'prize_pool' => 'Bs. 1,000 + Trofeo',
            'start_date' => now(),
            'status' => 'finished',
            'description' => 'Torneo rápido jugado desde el panel principal.',
            'created_by' => $user->id,
        ]);

        foreach ([$s1, $s2, $s3, $s4] as $index => $sq) {
            $tournament->squads()->attach($sq->id, [
                'registered_by' => $user->id,
                'seed_number' => $index + 1,
                'registered_at' => now(),
            ]);
        }

        $semi1WinnerA = rand(0, 10) >= 4;
        $semi1ScoreA = $semi1WinnerA ? 2 : rand(0, 1);
        $semi1ScoreB = $semi1WinnerA ? rand(0, 1) : 2;
        $finalist1 = $semi1WinnerA ? $s1 : $s2;

        $semi2WinnerA = rand(0, 10) >= 5;
        $semi2ScoreA = $semi2WinnerA ? 2 : rand(0, 1);
        $semi2ScoreB = $semi2WinnerA ? rand(0, 1) : 2;
        $finalist2 = $semi2WinnerA ? $s3 : $s4;

        $finalWinnerA = rand(0, 10) >= 4;
        $finalScoreA = $finalWinnerA ? 3 : rand(1, 2);
        $finalScoreB = $finalWinnerA ? rand(1, 2) : 3;
        $champion = $finalWinnerA ? $finalist1 : $finalist2;

        $m1 = GameMatch::create([
            'tournament_id' => $tournament->id,
            'round_name' => 'Semifinal 1',
            'match_number' => 1,
            'squad_a_id' => $s1->id,
            'squad_b_id' => $s2->id,
            'score_a' => $semi1ScoreA,
            'score_b' => $semi1ScoreB,
            'winner_squad_id' => $finalist1->id,
            'scheduled_at' => now(),
            'status' => 'finished',
            'lobby_code' => 'LOBBY-' . strtoupper(Str::random(4)),
        ]);

        $m2 = GameMatch::create([
            'tournament_id' => $tournament->id,
            'round_name' => 'Semifinal 2',
            'match_number' => 2,
            'squad_a_id' => $s3->id,
            'squad_b_id' => $s4->id,
            'score_a' => $semi2ScoreA,
            'score_b' => $semi2ScoreB,
            'winner_squad_id' => $finalist2->id,
            'scheduled_at' => now(),
            'status' => 'finished',
            'lobby_code' => 'LOBBY-' . strtoupper(Str::random(4)),
        ]);

        $m3 = GameMatch::create([
            'tournament_id' => $tournament->id,
            'round_name' => 'Gran Final',
            'match_number' => 3,
            'squad_a_id' => $finalist1->id,
            'squad_b_id' => $finalist2->id,
            'score_a' => $finalScoreA,
            'score_b' => $finalScoreB,
            'winner_squad_id' => $champion->id,
            'scheduled_at' => now(),
            'status' => 'finished',
            'lobby_code' => 'LOBBY-' . strtoupper(Str::random(4)),
        ]);

        $champion->increment('wins');

        SquadMessage::create([
            'squad_id' => $champion->id,
            'user_id' => $user->id,
            'message' => "¡Campeones de {$tournament->name}! Victoria {$finalScoreA}-{$finalScoreB} en la Gran Final.",
            'is_pinned' => true,
        ]);

        return response()->json([
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'game' => $tournament->game,
                'url' => route('tournaments.show', $tournament),
            ],
            'rounds' => [
                [
                    'round' => 'Semifinal 1',
                    'lobby' => $m1->lobby_code,
                    'squad_a' => ['name' => $s1->name, 'tag' => $s1->tag, 'color' => $s1->logo_color],
                    'squad_b' => ['name' => $s2->name, 'tag' => $s2->tag, 'color' => $s2->logo_color],
                    'score_a' => $semi1ScoreA,
                    'score_b' => $semi1ScoreB,
                    'winner' => ['name' => $finalist1->name, 'tag' => $finalist1->tag],
                ],
                [
                    'round' => 'Semifinal 2',
                    'lobby' => $m2->lobby_code,
                    'squad_a' => ['name' => $s3->name, 'tag' => $s3->tag, 'color' => $s3->logo_color],
                    'squad_b' => ['name' => $s4->name, 'tag' => $s4->tag, 'color' => $s4->logo_color],
                    'score_a' => $semi2ScoreA,
                    'score_b' => $semi2ScoreB,
                    'winner' => ['name' => $finalist2->name, 'tag' => $finalist2->tag],
                ],
                [
                    'round' => 'Gran Final',
                    'lobby' => $m3->lobby_code,
                    'squad_a' => ['name' => $finalist1->name, 'tag' => $finalist1->tag, 'color' => $finalist1->logo_color],
                    'squad_b' => ['name' => $finalist2->name, 'tag' => $finalist2->tag, 'color' => $finalist2->logo_color],
                    'score_a' => $finalScoreA,
                    'score_b' => $finalScoreB,
                    'winner' => ['name' => $champion->name, 'tag' => $champion->tag, 'color' => $champion->logo_color],
                ],
            ],
            'champion' => [
                'name' => $champion->name,
                'tag' => $champion->tag,
                'color' => $champion->logo_color,
                'wins' => $champion->wins,
            ],
        ]);
    }
}
