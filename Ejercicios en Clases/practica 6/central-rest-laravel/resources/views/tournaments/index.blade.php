@extends('layouts.app')

@section('title', 'Torneos Relámpago y Matchmaking — LobbyMatch')

@section('content')
<div class="space-y-8">
    
    <div class="cyber-card rounded-3xl p-6 sm:p-8 border border-violet-500/30">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-violet-400">LobbyMatch</span>
                <h1 class="font-gaming text-2xl sm:text-3xl font-bold text-white mt-1">TORNEOS RELÁMPAGO Y COMPETENCIAS</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Explora los torneos publicados con su estado actual (<span class="text-emerald-300">Abierto a inscripciones</span>, <span class="text-amber-300">En curso</span>, <span class="text-slate-300">Finalizado</span>) y filtra por juego o comunidad.
                </p>
            </div>
        </div>

        
        <form action="{{ route('tournaments.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="q" value="{{ $search }}"
                   placeholder="Buscar por nombre o palabra clave..."
                   class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs sm:text-sm text-white">

            <select name="game" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs sm:text-sm text-white">
                <option value="">Todos los videojuegos</option>
                @foreach(['Fortnite', 'Valorant', 'Dota 2', 'Counter-Strike 2', 'Mobile Legends'] as $g)
                    <option value="{{ $g }}" @selected($game === $g)>{{ $g }}</option>
                @endforeach
            </select>

            <select name="status" class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs sm:text-sm text-white">
                <option value="">Todos los estados</option>
                <option value="open" @selected($status === 'open')>Abierto a inscripciones</option>
                <option value="in_progress" @selected($status === 'in_progress')>En curso</option>
                <option value="finished" @selected($status === 'finished')>Finalizado</option>
            </select>

            <input type="text" name="zone" value="{{ $zone }}"
                   placeholder="Zona / Comunidad (Ej. USFX, Sucre)"
                   class="px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs sm:text-sm text-white">

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl font-bold text-xs sm:text-sm bg-gradient-to-r from-violet-600 to-cyan-500 text-white">
                    🔍 Filtrar
                </button>
                <a href="{{ route('tournaments.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs flex items-center">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    
    @auth
        @if(auth()->user()->isAdmin())
            <div class="cyber-card rounded-3xl p-6 sm:p-8 border border-amber-500/40 bg-gradient-to-br from-amber-500/5 via-[#0f1626] to-[#0a0f1d]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/40">
                            ⚡ Panel Exclusivo Administrador · LobbyMatch</span>
                        <h2 class="font-gaming text-xl font-bold text-white mt-2">PUBLICAR NUEVO TORNEO RELÁMPAGO</h2>
                    </div>
                </div>

                <form action="{{ route('tournaments.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Nombre Oficial del Torneo *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="Ej. Copa Universitaria USFX Fortnite Squads"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Videojuego *</label>
                            <select name="game" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                                <option value="Fortnite">Fortnite</option>
                                <option value="Valorant">Valorant</option>
                                <option value="Dota 2">Dota 2</option>
                                <option value="Counter-Strike 2">Counter-Strike 2</option>
                                <option value="Mobile Legends">Mobile Legends</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Fecha y Hora de Inicio *</label>
                            <input type="datetime-local" name="start_date" value="{{ old('start_date', now()->addDays(3)->format('Y-m-d\TH:i')) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Límite de Equipos *</label>
                            <input type="number" name="max_teams" value="{{ old('max_teams', 8) }}" min="2" max="64" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Mín. Integrantes por Squad *</label>
                            <input type="number" name="min_squad_members" value="{{ old('min_squad_members', 2) }}" min="1" max="10" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Estado Inicial *</label>
                            <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                                <option value="open">Abierto a inscripciones</option>
                                <option value="in_progress">En curso</option>
                                <option value="finished">Finalizado</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Comunidad / Zona *</label>
                            <input type="text" name="zone_or_community" value="{{ old('zone_or_community', 'USFX / Sucre') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Formato Competitivo *</label>
                            <input type="text" name="format" value="{{ old('format', 'Eliminación Directa BO3') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Bolsa de Premios *</label>
                            <input type="text" name="prize_pool" value="{{ old('prize_pool', 'Bs. 1,500 + Medallas') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Reglamento y Descripción del Torneo</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               placeholder="Detalles de mapas, servidor LATAM, puntualidad en el lobby..."
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                    </div>

                    <button type="submit"
                            class="px-6 py-3 rounded-xl font-bold text-sm bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 shadow-lg shadow-amber-500/20 transition">
                        ⚡ Publicar Torneo Relámpago
                    </button>
                </form>
            </div>
        @endif
    @endauth

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($tournaments as $tournament)
            <div class="cyber-card rounded-2xl p-6 flex flex-col justify-between">
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
                    <p class="text-xs text-cyan-400 font-medium mb-2">📍 {{ $tournament->zone_or_community }} · {{ $tournament->format }}</p>
                    <p class="text-xs text-slate-400 mb-4">{{ $tournament->description }}</p>
                </div>

                <div class="space-y-3 pt-4 border-t border-slate-800">
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="bg-slate-900/90 p-2 rounded-lg">
                            <span class="text-slate-500 block">Premio</span>
                            <span class="font-bold text-emerald-300">{{ $tournament->prize_pool }}</span>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg">
                            <span class="text-slate-500 block">Inscritos</span>
                            <span class="font-bold text-white">{{ $tournament->squads_count }} / {{ $tournament->max_teams }}</span>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-lg">
                            <span class="text-slate-500 block">Mín. Roster</span>
                            <span class="font-bold text-violet-300">{{ $tournament->min_squad_members }} jug.</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs text-slate-400">📅 {{ $tournament->start_date->format('d/m/Y H:i') }}</span>
                        <a href="{{ route('tournaments.show', $tournament) }}"
                           class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-violet-600 to-cyan-500 text-white hover:opacity-95 transition">
                            Ver Torneo / Inscribirse →
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full cyber-card rounded-2xl p-8 text-center text-slate-400">
                No se encontraron torneos con los filtros seleccionados.
            </div>
        @endforelse
    </div>
</div>
@endsection
