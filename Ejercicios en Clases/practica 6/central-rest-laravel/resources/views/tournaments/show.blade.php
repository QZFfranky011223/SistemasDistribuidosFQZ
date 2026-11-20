@extends('layouts.app')

@section('title', $tournament->name . ' — Torneo LobbyMatch')

@section('content')
<div class="space-y-8">
    
    <div class="cyber-card rounded-3xl p-6 sm:p-8 border border-violet-500/30">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-violet-500/20 text-violet-300 border border-violet-500/40">
                        {{ $tournament->game }}
                    </span>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-cyan-500/20 text-cyan-300">
                        📍 {{ $tournament->zone_or_community }}
                    </span>
                    @if($tournament->status === 'open')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            ● Abierto a inscripciones
                        </span>
                    @elseif($tournament->status === 'in_progress')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                            ⚡ En curso
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-700 text-slate-300">
                            ✓ Finalizado
                        </span>
                    @endif
                </div>

                <h1 class="font-gaming text-2xl sm:text-4xl font-black text-white">{{ $tournament->name }}</h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-3xl">{{ $tournament->description }}</p>

                <div class="flex flex-wrap items-center gap-4 pt-2 text-xs text-slate-400">
                    <span>🏆 Premio: <strong class="text-emerald-300">{{ $tournament->prize_pool }}</strong></span>
                    <span>📅 Inicio: <strong class="text-white">{{ $tournament->start_date->format('d/m/Y H:i') }}</strong></span>
                    <span>🛡️ Cupos: <strong class="text-cyan-300">{{ $tournament->squads->count() }} / {{ $tournament->max_teams }} Squads</strong></span>
                    <span>👥 Requisito Mínimo: <strong class="text-violet-300">{{ $tournament->min_squad_members }} integrantes activos</strong></span>
                </div>
            </div>

            
            <div class="w-full lg:w-auto flex flex-col items-stretch sm:items-end gap-2">
                @auth
                    @if($user->isAdmin())
                        <div class="flex flex-wrap items-center gap-2">
                            <form action="{{ route('tournaments.brackets', $tournament) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 transition">
                                    ⚡ Generar / Actualizar Llaves (Brackets)
                                </button>
                            </form>

                            <form action="{{ route('tournaments.status', $tournament) }}" method="POST" class="flex items-center gap-1">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl bg-slate-900 border border-amber-500/40 text-amber-300 text-xs font-semibold">
                                    <option value="open" @selected($tournament->status === 'open')>Estado: Abierto</option>
                                    <option value="in_progress" @selected($tournament->status === 'in_progress')>Estado: En curso</option>
                                    <option value="finished" @selected($tournament->status === 'finished')>Estado: Finalizado</option>
                                </select>
                            </form>
                        </div>
                    @else
                        @if($isRegistered)
                            <div class="px-5 py-3 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 text-xs font-bold text-center">
                                ✅ Tu Squad [{{ $activeSquad->tag }}] {{ $activeSquad->name }} está inscrito oficialmente
                            </div>
                        @elseif($tournament->status === 'open')
                            @if($activeSquad && (int) $activeSquad->leader_id === $user->id)
                                <form action="{{ route('tournaments.register', $tournament) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full px-6 py-3.5 rounded-2xl font-bold text-sm bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-slate-950 shadow-xl shadow-emerald-500/20 transition">
                                        🛡️ Inscribir a mi Squad [{{ $activeSquad->tag }}] ({{ $activeSquad->active_members_count }}/{{ $tournament->min_squad_members }} mín.)
                                    </button>
                                </form>
                            @elseif($activeSquad)
                                <div class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-slate-400">
                                    Solo el Capitán de <strong>{{ $activeSquad->name }}</strong> puede inscribir al equipo.
                                </div>
                            @else
                                <a href="{{ route('squads.index') }}" class="px-5 py-3 rounded-xl bg-violet-600 text-white text-xs font-bold text-center">
                                    Crea un Squad (serás Capitán) para inscribirte →
                                </a>
                            @endif
                        @endif
                    @endif
                @else
                    <a href="{{ route('login') }}" class="px-5 py-3 rounded-xl bg-violet-600 text-white text-xs font-bold">
                        Inicia sesión para inscribir a tu Squad
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-5 cyber-card rounded-2xl p-6">
            <h2 class="font-gaming text-lg font-bold text-white mb-4 flex items-center justify-between">
                <span>🛡️ Squads Participantes Oficiales</span>
                <span class="text-xs px-2.5 py-1 rounded bg-violet-500/20 text-violet-300">
                    {{ $tournament->squads->count() }} / {{ $tournament->max_teams }}
                </span>
            </h2>

            <div class="space-y-3">
                @forelse($tournament->squads as $squad)
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-6 text-xs font-gaming font-bold text-slate-500">#{{ $squad->pivot->seed_number ?? $loop->iteration }}</span>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-gaming font-bold text-xs text-white"
                                 style="background-color: {{ $squad->logo_color }}">
                                {{ $squad->tag }}
                            </div>
                            <div>
                                <a href="{{ route('squads.show', $squad) }}" class="font-bold text-sm text-white hover:text-cyan-300">
                                    {{ $squad->name }}
                                </a>
                                <div class="text-xs text-slate-400">
                                    Cap: {{ $squad->leader->gamerProfile->gamertag ?? $squad->leader->name }} · {{ $squad->active_members_count }} integrantes
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                            Verificado
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Aún no hay Squads inscritos en este torneo.</p>
                @endforelse
            </div>
        </div>

        
        <div class="lg:col-span-7 cyber-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-gaming text-lg font-bold text-white flex items-center gap-2">
                    <span>⚔️</span> Llaves de Enfrentamientos (Brackets / Matchmaking)
                </h2>
                <span class="text-xs text-cyan-400">{{ $tournament->format }}</span>
            </div>

            <div class="space-y-4">
                @forelse($tournament->matches as $match)
                    <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-gaming font-bold text-cyan-400">{{ $match->round_name }}</span>
                            <div class="flex items-center gap-2">
                                @if($match->lobby_code)
                                    <span class="text-slate-400">Lobby: <code class="text-violet-300 font-bold">{{ $match->lobby_code }}</code></span>
                                @endif
                                <span class="px-2.5 py-0.5 rounded font-semibold {{ $match->status === 'live' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : ($match->status === 'finished' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-300') }}">
                                    {{ $match->status_label }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-11 items-center gap-3 bg-slate-950/80 p-4 rounded-xl border border-slate-800/80">
                            <div class="sm:col-span-4 flex items-center justify-between sm:justify-start gap-2">
                                <span class="font-bold text-sm {{ $match->winner_squad_id && $match->winner_squad_id === $match->squad_a_id ? 'text-emerald-400' : 'text-white' }}">
                                    [{{ $match->squadA->tag ?? 'TBD' }}] {{ $match->squadA->name ?? 'Por definir' }}
                                </span>
                            </div>

                            <div class="sm:col-span-3 text-center">
                                <span class="inline-block px-4 py-1.5 rounded-xl bg-slate-900 border border-slate-700 font-gaming font-black text-base text-amber-300">
                                    {{ $match->score_a }} : {{ $match->score_b }}
                                </span>
                            </div>

                            <div class="sm:col-span-4 flex items-center justify-between sm:justify-end gap-2 text-right">
                                <span class="font-bold text-sm {{ $match->winner_squad_id && $match->winner_squad_id === $match->squad_b_id ? 'text-emerald-400' : 'text-white' }}">
                                    [{{ $match->squadB->tag ?? 'BYE' }}] {{ $match->squadB->name ?? 'Pase Directo' }}
                                </span>
                            </div>
                        </div>

                        @if($match->winnerSquad)
                            <div class="text-xs text-emerald-400 font-semibold">
                                🏆 Ganador del enfrentamiento: <strong>[{{ $match->winnerSquad->tag }}] {{ $match->winnerSquad->name }}</strong>
                            </div>
                        @endif

                        
                        @auth
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('matches.update', $match) }}" method="POST"
                                      class="pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2 text-xs">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex items-center gap-2">
                                        <span class="text-amber-300 font-semibold">Admin Score:</span>
                                        <input type="number" name="score_a" value="{{ $match->score_a }}" min="0" max="99"
                                               class="w-16 px-2 py-1 rounded bg-slate-950 border border-slate-700 text-white text-center">
                                        <span>vs</span>
                                        <input type="number" name="score_b" value="{{ $match->score_b }}" min="0" max="99"
                                               class="w-16 px-2 py-1 rounded bg-slate-950 border border-slate-700 text-white text-center">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <select name="status" class="px-2.5 py-1 rounded bg-slate-950 border border-slate-700 text-white">
                                            <option value="scheduled" @selected($match->status === 'scheduled')>Programada</option>
                                            <option value="live" @selected($match->status === 'live')>En vivo</option>
                                            <option value="finished" @selected($match->status === 'finished')>Finalizada</option>
                                        </select>
                                        <button type="submit" class="px-3 py-1 rounded bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 font-bold">
                                            Guardar Resultado
                                        </button>
                                    </div>
                                </form>
                            @endif
                        @endauth
                    </div>
                @empty
                    <div class="p-6 rounded-2xl bg-slate-900/70 border border-dashed border-slate-700 text-center text-xs text-slate-400">
                        Los enfrentamientos aún no han sido sorteados. El Administrador puede generarlos con un clic cuando haya al menos 2 Squads inscritos.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
