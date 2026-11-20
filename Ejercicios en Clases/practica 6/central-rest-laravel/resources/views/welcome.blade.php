@extends('layouts.app')

@section('title', 'LobbyMatch — Torneos Relámpago, Matchmaking y Squads eSports')

@section('content')
    
    <section class="relative overflow-hidden rounded-3xl border border-violet-500/30 bg-gradient-to-br from-[#121a30] via-[#0d1324] to-[#080d1a] p-8 sm:p-12 shadow-2xl mb-10">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-violet-600/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-cyan-500/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-violet-500/15 border border-violet-500/30 text-violet-300 text-xs font-semibold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                    COMUNIDAD UNIVERSITARIA USFX & SUCRE ESPORTS
                </div>

                <h1 class="font-gaming text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight">
                    ARMA TU <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-cyan-400 bg-clip-text text-transparent">SQUAD</span>.
                    DOMINA LOS <span class="bg-gradient-to-r from-cyan-400 to-emerald-400 bg-clip-text text-transparent">TORNEOS RELÁMPAGO</span>.
                </h1>

                <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-2xl">
                    <strong>LobbyMatch</strong> conecta a jugadores de <strong>Fortnite, Valorant, Dota 2, CS2 y Mobile Legends</strong>. Crea tu perfil de Gamer, genera un código único de invitación para tu clan, inscríbete en brackets automáticos y construye tu reputación competitiva.
                </p>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-6 py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-violet-600 to-cyan-500 hover:from-violet-500 hover:to-cyan-400 text-white shadow-lg shadow-violet-600/30 transition">
                            🚀 Ir a mi Dashboard Gamer
                        </a>
                        <a href="{{ route('squads.index') }}" class="px-6 py-3.5 rounded-xl font-semibold text-sm bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 border border-slate-700 transition">
                            🛡️ Gestionar mi Squad
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-6 py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-violet-600 to-cyan-500 hover:from-violet-500 hover:to-cyan-400 text-white shadow-lg shadow-violet-600/30 transition">
                            🎮 Crear Cuenta de Gamer
                        </a>
                        <a href="{{ route('tournaments.index') }}" class="px-6 py-3.5 rounded-xl font-semibold text-sm bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 border border-slate-700 transition">
                            🏆 Explorar Torneos Abiertos
                        </a>
                    @endauth
                </div>
            </div>

            
            <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                <div class="cyber-card rounded-2xl p-5 border border-violet-500/25">
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Gamers Activos</div>
                    <div class="font-gaming text-3xl font-extrabold text-white mt-2">{{ $stats['players'] }}</div>
                    <div class="text-xs text-violet-400 mt-1">Perfiles verificados con ID</div>
                </div>
                <div class="cyber-card rounded-2xl p-5 border border-cyan-500/25">
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Squads Oficiales</div>
                    <div class="font-gaming text-3xl font-extrabold text-cyan-300 mt-2">{{ $stats['squads'] }}</div>
                    <div class="text-xs text-cyan-400 mt-1">Con código de invitación</div>
                </div>
                <div class="cyber-card rounded-2xl p-5 border border-emerald-500/25">
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Torneos Publicados</div>
                    <div class="font-gaming text-3xl font-extrabold text-emerald-300 mt-2">{{ $stats['tournaments'] }}</div>
                    <div class="text-xs text-emerald-400 mt-1">Abiertos, en curso y finalizados</div>
                </div>
                <div class="cyber-card rounded-2xl p-5 border border-amber-500/25">
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Partidas / Brackets</div>
                    <div class="font-gaming text-3xl font-extrabold text-amber-300 mt-2">{{ $stats['matches'] }}</div>
                    <div class="text-xs text-amber-400 mt-1">Matchmaking automatizado</div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="mb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="font-gaming text-2xl font-bold text-white flex items-center gap-2">
                    <span>🏆</span> Torneos Relámpago y Competencias
                </h2>
                <p class="text-sm text-slate-400">Inscribe a tu Squad y compite en las llaves oficiales de la comunidad.</p>
            </div>
            <a href="{{ route('tournaments.index') }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">
                Ver todos los torneos y filtros →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredTournaments as $tournament)
                <div class="cyber-card rounded-2xl p-6 flex flex-col justify-between transition">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-violet-500/20 text-violet-300 border border-violet-500/30">
                                {{ $tournament->game }}
                            </span>
                            @if($tournament->status === 'open')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                    ● Abierto a inscripciones
                                </span>
                            @elseif($tournament->status === 'in_progress')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                    ⚡ En curso
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-700/60 text-slate-300 border border-slate-600">
                                    ✓ Finalizado
                                </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-white mb-1">{{ $tournament->name }}</h3>
                        <p class="text-xs text-cyan-400 font-medium mb-3">📍 {{ $tournament->zone_or_community }} · {{ $tournament->format }}</p>
                        <p class="text-xs text-slate-400 line-clamp-2 mb-4">{{ $tournament->description }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-800/80 space-y-3">
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="bg-slate-900/80 p-2 rounded-lg">
                                <span class="text-slate-500 block">Premio</span>
                                <span class="font-bold text-emerald-300">{{ $tournament->prize_pool }}</span>
                            </div>
                            <div class="bg-slate-900/80 p-2 rounded-lg">
                                <span class="text-slate-500 block">Cupos Squads</span>
                                <span class="font-bold text-white">{{ $tournament->squads_count }} / {{ $tournament->max_teams }}</span>
                            </div>
                            <div class="bg-slate-900/80 p-2 rounded-lg">
                                <span class="text-slate-500 block">Mín. Roster</span>
                                <span class="font-bold text-violet-300">{{ $tournament->min_squad_members }} jug.</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs text-slate-400">
                                📅 {{ $tournament->start_date->format('d/m/Y H:i') }}
                            </span>
                            <a href="{{ route('tournaments.show', $tournament) }}"
                               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-violet-600 hover:bg-violet-500 text-white transition">
                                Ver Torneo & Brackets
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-7 cyber-card rounded-2xl p-6">
            <h3 class="font-gaming text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>⚔️</span> Enfrentamientos y Brackets Recientes
            </h3>
            <div class="space-y-3">
                @forelse($liveMatches as $match)
                    <div class="p-4 rounded-xl bg-slate-900/85 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <span class="text-[11px] uppercase tracking-wider text-cyan-400 font-semibold">
                                {{ $match->tournament->name ?? 'Torneo' }} · {{ $match->round_name }}
                            </span>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="font-bold text-white">
                                    [{{ $match->squadA->tag ?? 'TBD' }}] {{ $match->squadA->name ?? 'Por definir' }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded bg-slate-800 font-gaming font-bold text-sm text-amber-300">
                                    {{ $match->score_a }} : {{ $match->score_b }}
                                </span>
                                <span class="font-bold text-white">
                                    [{{ $match->squadB->tag ?? 'TBD' }}] {{ $match->squadB->name ?? 'Por definir' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded text-xs font-semibold {{ $match->status === 'live' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : ($match->status === 'finished' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-300') }}">
                                {{ $match->status_label }}
                            </span>
                            @if($match->lobby_code)
                                <div class="text-[11px] text-slate-400 mt-1">Sala: <code class="text-violet-300">{{ $match->lobby_code }}</code></div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No hay partidas programadas aún.</p>
                @endforelse
            </div>
        </div>

        
        <div class="lg:col-span-5 cyber-card rounded-2xl p-6">
            <h3 class="font-gaming text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>🛡️</span> Top Squads de la Comunidad
            </h3>
            <div class="space-y-3">
                @foreach($topSquads as $index => $squad)
                    <div class="p-3.5 rounded-xl bg-slate-900/85 border border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center font-gaming font-bold text-xs text-white"
                                 style="background-color: {{ $squad->logo_color }}">
                                {{ $squad->tag }}
                            </div>
                            <div>
                                <div class="font-semibold text-sm text-white">{{ $squad->name }}</div>
                                <div class="text-xs text-slate-400">{{ $squad->primary_game }} · {{ $squad->active_members_count }} miembros</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-gaming font-bold text-sm text-emerald-400">{{ $squad->wins }} W</span>
                            <span class="block text-[11px] text-slate-500">{{ $squad->region }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
