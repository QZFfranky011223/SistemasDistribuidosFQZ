<?php

namespace App\Http\Controllers;

use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\SquadMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SquadController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $activeSquad = $user->squads()
            ->wherePivot('status', 'active')
            ->with([
                'leader.gamerProfile',
                'activeMembers.gamerProfile',
                'pendingMembers.gamerProfile',
                'messages.user.gamerProfile',
                'tournaments',
            ])
            ->first();

        $search = $request->string('q')->trim()->toString();
        $gameFilter = $request->string('game')->trim()->toString();

        $squadsQuery = Squad::with(['leader.gamerProfile', 'activeMembers.gamerProfile'])
            ->withCount('activeMembers')
            ->latest();

        if ($search !== '') {
            $squadsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tag', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($gameFilter !== '') {
            $squadsQuery->where('primary_game', $gameFilter);
        }

        $squads = $squadsQuery->get();

        return view('squads.index', compact('user', 'activeSquad', 'squads', 'search', 'gameFilter'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->activeSquad()) {
            return back()->withErrors([
                'name' => 'Ya perteneces a un Squad activo.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:squads,name'],
            'tag' => ['required', 'string', 'min:2', 'max:6', 'alpha_num', 'unique:squads,tag'],
            'primary_game' => ['required', 'string', 'max:80'],
            'region' => ['required', 'string', 'max:80'],
            'logo_color' => ['nullable', 'string', 'max:20'],
            'max_members' => ['nullable', 'integer', 'min:2', 'max:10'],
            'discord_invite' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:400'],
        ]);

        $squad = Squad::create([
            'name' => $validated['name'],
            'tag' => strtoupper($validated['tag']),
            'logo_color' => $validated['logo_color'] ?? '#8b5cf6',
            'primary_game' => $validated['primary_game'],
            'region' => $validated['region'],
            'description' => $validated['description'] ?? null,
            'discord_invite' => $validated['discord_invite'] ?? null,
            'leader_id' => $user->id,
            'invite_code' => Squad::generateInviteCode(),
            'max_members' => $validated['max_members'] ?? 5,
            'is_recruiting' => true,
        ]);

        SquadMember::create([
            'squad_id' => $squad->id,
            'user_id' => $user->id,
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        if ($user->role === 'player') {
            $user->update(['role' => 'captain']);
        }

        SquadMessage::create([
            'squad_id' => $squad->id,
            'user_id' => $user->id,
            'message' => "Squad [{$squad->tag}] {$squad->name} creado. Código: {$squad->invite_code}",
            'is_pinned' => true,
        ]);

        return redirect()->route('squads.show', $squad)
            ->with('success', "Squad [{$squad->tag}] {$squad->name} creado con código {$squad->invite_code}.");
    }

    public function show(Squad $squad): View
    {
        /** @var User $user */
        $user = Auth::user();

        $squad->load([
            'leader.gamerProfile',
            'activeMembers.gamerProfile',
            'pendingMembers.gamerProfile',
            'tournaments' => fn ($q) => $q->withCount('squads'),
            'messages.user.gamerProfile',
        ]);

        $isMember = $squad->activeMembers->contains('id', $user->id);
        $isCaptain = (int) $squad->leader_id === $user->id || $user->isAdmin();

        return view('squads.show', compact('squad', 'user', 'isMember', 'isCaptain'));
    }

    public function joinByCode(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'max:20'],
        ]);

        if ($user->activeSquad()) {
            return back()->withErrors([
                'invite_code' => 'Ya perteneces a un Squad activo.',
            ]);
        }

        $code = strtoupper(trim($validated['invite_code']));
        $squad = Squad::where('invite_code', $code)->first();

        if (! $squad) {
            return back()->withErrors([
                'invite_code' => 'El código de invitación no es válido.',
            ]);
        }

        if ($squad->activeMembers()->count() >= $squad->max_members) {
            return back()->withErrors([
                'invite_code' => 'El Squad ya alcanzó el límite de integrantes.',
            ]);
        }

        SquadMember::updateOrCreate(
            [
                'squad_id' => $squad->id,
                'user_id' => $user->id,
            ],
            [
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        SquadMessage::create([
            'squad_id' => $squad->id,
            'user_id' => $user->id,
            'message' => "{$user->name} se unió al equipo.",
            'is_pinned' => false,
        ]);

        return redirect()->route('squads.show', $squad)
            ->with('success', "Te has unido a [{$squad->tag}] {$squad->name}.");
    }

    public function requestJoin(Squad $squad): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->activeSquad()) {
            return back()->withErrors([
                'squad' => 'Ya perteneces a un Squad activo.',
            ]);
        }

        if ($squad->activeMembers()->count() >= $squad->max_members) {
            return back()->withErrors([
                'squad' => 'El Squad ya tiene su roster completo.',
            ]);
        }

        SquadMember::updateOrCreate(
            [
                'squad_id' => $squad->id,
                'user_id' => $user->id,
            ],
            [
                'role' => 'member',
                'status' => 'pending',
                'joined_at' => now(),
            ]
        );

        return back()->with('success', "Solicitud enviada a [{$squad->tag}] {$squad->name}.");
    }

    public function approveMember(Squad $squad, User $member): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ((int) $squad->leader_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if ($member->activeSquad()) {
            return back()->withErrors([
                'member' => 'El jugador ya pertenece a otro Squad.',
            ]);
        }

        SquadMember::where('squad_id', $squad->id)
            ->where('user_id', $member->id)
            ->update([
                'status' => 'active',
                'joined_at' => now(),
            ]);

        return back()->with('success', "{$member->name} fue aceptado en el equipo.");
    }

    public function leave(Squad $squad): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ((int) $squad->leader_id === $user->id) {
            return back()->withErrors([
                'squad' => 'Eres el capitán del Squad.',
            ]);
        }

        SquadMember::where('squad_id', $squad->id)
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('squads.index')
            ->with('success', "Saliste de [{$squad->tag}] {$squad->name}.");
    }

    public function storeMessage(Request $request, Squad $squad): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $isMember = $squad->activeMembers()->where('users.id', $user->id)->exists();
        if (! $isMember && ! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        SquadMessage::create([
            'squad_id' => $squad->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_pinned' => $request->boolean('is_pinned') && ((int) $squad->leader_id === $user->id || $user->isAdmin()),
        ]);

        return back()->with('success', 'Mensaje enviado.');
    }
}
