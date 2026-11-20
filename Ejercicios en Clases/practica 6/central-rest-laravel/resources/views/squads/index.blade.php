@extends('layouts.app')

@section('title', 'Gestión de Escuadras (Squads) — LobbyMatch')

@section('content')
<div class="space-y-8">
    
    <div class="cyber-card rounded-3xl p-6 sm:p-8 border border-violet-500/30 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-violet-400">LobbyMatch</span>
            <h1 class="font-gaming text-2xl sm:text-3xl font-bold text-white mt-1">GESTOR DE ESCUADRAS (SQUADS)</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Funda tu propio clan como Capitán con código de invitación único o únete al roster oficial de una escuadra existente.
            </p>
        </div>

        @if($activeSquad)
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center font-gaming font-bold text-white text-sm"
                     style="background-color: {{ $activeSquad->logo_color }}">
                    {{ $activeSquad->tag }}
                </div>
                <div>
                    <span class="text-[11px] uppercase tracking-wider text-emerald-300 font-bold block">Tu Squad Activo</span>
                    <div class="font-bold text-white text-sm">{{ $activeSquad->name }} (<code class="text-cyan-300">{{ $activeSquad->invite_code }}</code>)</div>
                </div>
                <a href="{{ route('squads.show', $activeSquad) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                    Ver Mi Squad →
                </a>
            </div>
        @endif
    </div>

    
    @if(! $activeSquad && ! $user->isAdmin())
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-7 cyber-card rounded-3xl p-6 sm:p-8 border border-violet-500/30">
                <span class="text-xs font-bold uppercase tracking-wider text-violet-400">LobbyMatch</span>
                <h2 class="font-gaming text-xl font-bold text-white mt-1 mb-2">FUNDAR UN NUEVO SQUAD (SERÁS CAPITÁN)</h2>
                <p class="text-xs text-slate-400 mb-5">
                    El sistema te asignará automáticamente como <strong>Capitán</strong> y generará un código de invitación único (<code class="text-cyan-300">LM-XXXXXX</code>) para reclutar a tu roster.
                </p>

                <form action="{{ route('squads.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Nombre Único del Squad *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="Ej. Sucre Vipers eSports"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Etiqueta / Tag *</label>
                            <input type="text" name="tag" value="{{ old('tag') }}" required maxlength="6"
                                   placeholder="Ej. SVPR"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm uppercase focus:outline-none focus:border-violet-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Juego Principal *</label>
                            <select name="primary_game" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                                <option value="Fortnite">Fortnite</option>
                                <option value="Valorant">Valorant</option>
                                <option value="Dota 2">Dota 2</option>
                                <option value="Counter-Strike 2">Counter-Strike 2</option>
                                <option value="Mobile Legends">Mobile Legends</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Comunidad / Región *</label>
                            <input type="text" name="region" value="{{ old('region', 'Sucre / USFX') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Color de Emblema</label>
                            <select name="logo_color" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                                <option value="#8b5cf6">Violeta Neón</option>
                                <option value="#06b6d4">Cian Cibernético</option>
                                <option value="#10b981">Esmeralda</option>
                                <option value="#f43f5e">Carmesí</option>
                                <option value="#f59e0b">Dorado</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Servidor Discord / Contacto del Equipo</label>
                            <input type="text" name="discord_invite" value="{{ old('discord_invite') }}"
                                   placeholder="Ej. discord.gg/sucrevipers"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Límite de Integrantes</label>
                            <input type="number" name="max_members" value="{{ old('max_members', 5) }}" min="2" max="10"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Descripción y Palabras Clave de Reclutamiento</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               placeholder="Ej. Buscamos jugadores de PC y Consola en Sucre para torneos nocturnos."
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                    </div>

                    <button type="submit"
                            class="px-6 py-3 rounded-xl font-bold text-sm bg-gradient-to-r from-violet-600 to-cyan-500 hover:from-violet-500 hover:to-cyan-400 text-white shadow-lg shadow-violet-600/30 transition">
                        🛡️ Crear Squad y Obtener Código de Invitación
                    </button>
                </form>
            </div>

            
            <div class="lg:col-span-5 cyber-card rounded-3xl p-6 sm:p-8 border border-cyan-500/30 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-400">LobbyMatch</span>
                    <h2 class="font-gaming text-xl font-bold text-white mt-1 mb-2">INGRESAR CON CÓDIGO DE INVITACIÓN</h2>
                    <p class="text-xs text-slate-400 mb-5">
                        ¿Tu Capitán te compartió un código único? Ingrésalo aquí para integrarte inmediatamente al roster oficial del Squad.
                    </p>

                    <form action="{{ route('squads.join.code') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Código de Invitación del Squad *</label>
                            <input type="text" name="invite_code" required
                                   placeholder="Ej. LM-USFX01"
                                   class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-cyan-500/50 text-cyan-300 font-gaming font-bold text-base uppercase tracking-widest focus:outline-none focus:border-cyan-400">
                        </div>

                        <button type="submit"
                                class="w-full py-3 rounded-xl font-bold text-sm bg-cyan-500 hover:bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-500/20 transition">
                            🔑 Validar Código y Unirme al Roster
                        </button>
                    </form>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-slate-900/90 border border-slate-800 text-xs text-slate-400 space-y-1.5">
                    <div class="font-semibold text-slate-200">Códigos de Squads de prueba disponibles:</div>
                    @foreach($squads->take(3) as $sampleSquad)
                        <div class="flex items-center justify-between">
                            <span>[{{ $sampleSquad->tag }}] {{ $sampleSquad->name }}</span>
                            <code class="text-cyan-300 font-bold">{{ $sampleSquad->invite_code }}</code>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="font-gaming text-xl font-bold text-white">DIRECTORIO OFICIAL DE ESCUADRAS ({{ $squads->count() }})</h2>
            <form action="{{ route('squads.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nombre, tag o región..."
                       class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white">
                <select name="game" class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white">
                    <option value="">Todos los juegos</option>
                    @foreach(['Fortnite', 'Valorant', 'Dota 2', 'Counter-Strike 2', 'Mobile Legends'] as $g)
                        <option value="{{ $g }}" @selected($gameFilter === $g)>{{ $g }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold">
                    Filtrar
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($squads as $squad)
                <div class="cyber-card rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center font-gaming font-black text-sm text-white shadow-lg"
                                     style="background-color: {{ $squad->logo_color }}">
                                    {{ $squad->tag }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">{{ $squad->name }}</h3>
                                    <span class="text-xs text-cyan-400">{{ $squad->primary_game }} · {{ $squad->region }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-900 text-slate-200 border border-slate-700">
                                {{ $squad->active_members_count }}/{{ $squad->max_members }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-400 mb-4">{{ $squad->description ?: 'Escuadra competitiva lista para torneos relámpago.' }}</p>

                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-xs space-y-1 mb-4">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Capitán:</span>
                                <span class="text-white font-semibold">{{ $squad->leader->gamerProfile->gamertag ?? $squad->leader->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Código Invitación:</span>
                                <code class="text-cyan-300 font-bold">{{ $squad->invite_code }}</code>
                            </div>
                            @if($squad->discord_invite)
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Contacto:</span>
                                    <span class="text-indigo-300">{{ $squad->discord_invite }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-800">
                        <a href="{{ route('squads.show', $squad) }}"
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-white transition">
                            Ver Roster & Sala
                        </a>

                        @if(! $activeSquad && ! $user->isAdmin() && $squad->active_members_count < $squad->max_members)
                            <form action="{{ route('squads.request.join', $squad) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-violet-600/25 hover:bg-violet-600 text-violet-200 hover:text-white border border-violet-500/40 transition">
                                    Solicitar Ingreso
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
