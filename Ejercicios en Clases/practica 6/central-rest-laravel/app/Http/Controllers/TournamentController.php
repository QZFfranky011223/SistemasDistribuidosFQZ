<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Squad;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $game = $request->string('game')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $zone = $request->string('zone')->trim()->toString();

        $query = Tournament::with(['creator', 'squads'])
            ->withCount('squads')
            ->orderByRaw("CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 ELSE 3 END")
            ->orderBy('start_date');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('zone_or_community', 'like', "%{$search}%");
            });
        }

        if ($game !== '') {
            $query->where('game', $game);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($zone !== '') {
            $query->where('zone_or_community', 'like', "%{$zone}%");
        }

        $tournaments = $query->get();

        /** @var User|null $user */
        $user = Auth::user();
        $activeSquad = $user ? $user->activeSquad() : null;
        if ($activeSquad) {
            $activeSquad->loadCount('activeMembers');
        }

        return view('tournaments.index', compact(
            'tournaments',
            'search',
            'game',
            'status',
            'zone',
            'user',
            'activeSquad'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'game' => ['required', 'string', 'max:80'],
            'zone_or_community' => ['required', 'string', 'max:100'],
            'format' => ['required', 'string', 'max:80'],
            'min_squad_members' => ['required', 'integer', 'min:1', 'max:10'],
            'max_teams' => ['required', 'integer', 'min:2', 'max:64'],
            'prize_pool' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'status' => ['required', 'in:open,in_progress,finished'],
            'description' => ['nullable', 'string', 'max:600'],
        ]);

        $tournament = Tournament::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', "Torneo '{$tournament->name}' publicado.");
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load([
            'creator',
            'squads' => fn ($q) => $q->with(['leader.gamerProfile'])->withCount('activeMembers'),
            'matches' => fn ($q) => $q->with(['squadA', 'squadB', 'winnerSquad'])->orderBy('match_number'),
        ]);

        /** @var User|null $user */
        $user = Auth::user();
        $activeSquad = $user ? $user->activeSquad() : null;
        if ($activeSquad) {
            $activeSquad->loadCount('activeMembers');
        }

        $isRegistered = $activeSquad
            ? $tournament->squads->contains('id', $activeSquad->id)
            : false;

        return view('tournaments.show', compact('tournament', 'user', 'activeSquad', 'isRegistered'));
    }

    public function registerSquad(Request $request, Tournament $tournament): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $squad = $user->activeSquad();

        if (! $squad) {
            return back()->withErrors([
                'inscription' => 'No perteneces a ningún Squad activo.',
            ]);
        }

        if ((int) $squad->leader_id !== $user->id && ! $user->isAdmin()) {
            return back()->withErrors([
                'inscription' => 'Solo el capitán del Squad puede inscribir al equipo.',
            ]);
        }

        if ($tournament->status !== 'open') {
            return back()->withErrors([
                'inscription' => 'Este torneo no está abierto a inscripciones.',
            ]);
        }

        if ($tournament->squads()->where('squads.id', $squad->id)->exists()) {
            return back()->withErrors([
                'inscription' => 'Tu Squad ya está inscrito en este torneo.',
            ]);
        }

        if ($tournament->isFull()) {
            return back()->withErrors([
                'inscription' => 'El torneo ya alcanzó el límite de equipos.',
            ]);
        }

        $memberCount = $squad->activeMembers()->count();
        if ($memberCount < $tournament->min_squad_members) {
            return back()->withErrors([
                'inscription' => "Tu Squad tiene {$memberCount} integrante(s), pero el torneo exige mínimo {$tournament->min_squad_members}.",
            ]);
        }

        $seed = $tournament->squads()->count() + 1;

        $tournament->squads()->attach($squad->id, [
            'registered_by' => $user->id,
            'seed_number' => $seed,
            'registered_at' => now(),
        ]);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', "Squad [{$squad->tag}] {$squad->name} inscrito en {$tournament->name}.");
    }

    public function generateBrackets(Tournament $tournament): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            abort(403);
        }

        $squads = $tournament->squads()->orderByPivot('seed_number')->get();

        if ($squads->count() < 2) {
            return back()->withErrors([
                'brackets' => 'Se necesitan al menos 2 Squads inscritos para generar llaves.',
            ]);
        }

        $tournament->matches()->delete();

        $roundName = match (true) {
            $squads->count() <= 2 => 'Gran Final',
            $squads->count() <= 4 => 'Semifinal',
            default => 'Cuartos de Final',
        };

        $matchNumber = 1;
        $chunks = $squads->values()->chunk(2);

        foreach ($chunks as $pair) {
            $pairValues = $pair->values();
            $squadA = $pairValues->get(0);
            $squadB = $pairValues->get(1);

            GameMatch::create([
                'tournament_id' => $tournament->id,
                'round_name' => $roundName . ' #' . $matchNumber,
                'match_number' => $matchNumber,
                'squad_a_id' => $squadA?->id,
                'squad_b_id' => $squadB?->id,
                'score_a' => 0,
                'score_b' => 0,
                'winner_squad_id' => $squadB ? null : $squadA?->id,
                'scheduled_at' => $tournament->start_date->copy()->addMinutes(($matchNumber - 1) * 45),
                'status' => $squadB ? 'scheduled' : 'finished',
                'lobby_code' => 'LOBBY-' . strtoupper(Str::random(4)),
            ]);

            $matchNumber++;
        }

        if ($tournament->status === 'open') {
            $tournament->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Llaves del torneo generadas.');
    }

    public function updateStatus(Request $request, Tournament $tournament): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,finished'],
        ]);

        $tournament->update(['status' => $validated['status']]);

        return back()->with('success', 'Estado actualizado a: ' . $tournament->status_label);
    }

    public function updateMatch(Request $request, GameMatch $match): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'score_a' => ['required', 'integer', 'min:0', 'max:99'],
            'score_b' => ['required', 'integer', 'min:0', 'max:99'],
            'status' => ['required', 'in:scheduled,live,finished'],
        ]);

        $winnerId = null;
        if ($validated['status'] === 'finished') {
            if ($validated['score_a'] > $validated['score_b']) {
                $winnerId = $match->squad_a_id;
            } elseif ($validated['score_b'] > $validated['score_a']) {
                $winnerId = $match->squad_b_id;
            }
        }

        $wasFinished = $match->status === 'finished';

        $match->update([
            'score_a' => $validated['score_a'],
            'score_b' => $validated['score_b'],
            'status' => $validated['status'],
            'winner_squad_id' => $winnerId,
        ]);

        if (! $wasFinished && $validated['status'] === 'finished' && $winnerId) {
            Squad::where('id', $winnerId)->increment('wins');
        }

        return back()->with('success', "Marcador de la partida #{$match->match_number} guardado.");
    }
}
