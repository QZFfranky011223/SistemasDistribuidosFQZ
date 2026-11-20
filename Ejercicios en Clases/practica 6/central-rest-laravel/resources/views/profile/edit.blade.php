@extends('layouts.app')

@section('title', 'Configuración del Perfil de Gamer — LobbyMatch')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <div class="lg:col-span-7 cyber-card rounded-3xl p-6 sm:p-8 border border-violet-500/30">
        <div class="mb-6">
            <span class="text-xs font-bold uppercase tracking-wider text-violet-400">LobbyMatch</span>
            <h1 class="font-gaming text-2xl font-bold text-white mt-1">CONFIGURACIÓN DEL PERFIL DE GAMER</h1>
            <p class="text-xs text-slate-400 mt-1">
                Personaliza tu Gamertag, plataforma principal, ID competitivo y canales de contacto para que otros capitanes te reconozcan.
            </p>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Nombre del Usuario *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-cyan-300 mb-1.5">Apodo / Gamertag *</label>
                    <input type="text" name="gamertag" value="{{ old('gamertag', $profile->gamertag) }}" required
                           placeholder="Ej.PhantomUSFX"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-cyan-500/50 text-white text-sm focus:outline-none focus:border-cyan-400">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Plataforma Principal *</label>
                    <select name="platform" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                        @foreach(['PC', 'Consola', 'Móvil'] as $plat)
                            <option value="{{ $plat }}" @selected(old('platform', $profile->platform) === $plat)>{{ $plat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Juego Favorito *</label>
                    <select name="favorite_game" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                        @foreach(['Fortnite', 'Valorant', 'Dota 2', 'Counter-Strike 2', 'Mobile Legends', 'League of Legends', 'Free Fire'] as $g)
                            <option value="{{ $g }}" @selected(old('favorite_game', $profile->favorite_game) === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Rango Actual *</label>
                    <select name="rank_tier" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                        @foreach(['Bronce', 'Plata', 'Oro I', 'Platino', 'Diamante II', 'Ascendente', 'Inmortal', 'Unreal / Radiante'] as $rank)
                            <option value="{{ $rank }}" @selected(old('rank_tier', $profile->rank_tier) === $rank)>{{ $rank }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-emerald-300 mb-1.5">ID del Juego (Epic Games / Riot / Steam) *</label>
                    <input type="text" name="game_id" value="{{ old('game_id', $profile->game_id) }}" required
                           placeholder="Ej. EpicID_Sucre99 o RiotTag#BO1"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-emerald-500/40 text-white text-sm focus:outline-none focus:border-emerald-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-indigo-300 mb-1.5">Usuario de Discord (Contacto 1-Click)</label>
                    <input type="text" name="discord_handle" value="{{ old('discord_handle', $profile->discord_handle) }}"
                           placeholder="Ej. phantom_bo#1337"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-indigo-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Canal de Stream / Red Gaming (Twitch, Kick, YouTube)</label>
                <input type="text" name="stream_url" value="{{ old('stream_url', $profile->stream_url) }}"
                       placeholder="https://twitch.tv/tu_gamertag"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Biografía Competitiva / Palabras Clave de Rol (IGL, Sniper, Support, Entry)</label>
                <textarea name="bio" rows="3"
                          placeholder="Describe tu rol en el equipo, horarios disponibles y experiencia en torneos..."
                          class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">{{ old('bio', $profile->bio) }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-violet-600 to-cyan-500 hover:from-violet-500 hover:to-cyan-400 text-white shadow-lg shadow-violet-600/30 transition">
                    💾 Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    
    <div class="lg:col-span-5 space-y-6">
        
        <div class="cyber-card rounded-3xl p-6 border border-cyan-500/30">
            <h2 class="font-gaming text-base font-bold text-white mb-4">TARJETA PÚBLICA DE GAMER</h2>
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-[#11192e] to-slate-950 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] uppercase tracking-widest text-cyan-400 font-bold">{{ $profile->favorite_game }} · {{ $profile->platform }}</span>
                        <h3 class="font-gaming text-xl font-black text-white">{{ $profile->gamertag }}</h3>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                            ★ {{ number_format((float) $profile->reputation_score, 2) }} GG
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
                        <span class="text-slate-500 block">ID del Juego</span>
                        <code class="text-emerald-300 font-bold">{{ $profile->game_id }}</code>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
                        <span class="text-slate-500 block">Rango Competitivo</span>
                        <span class="text-violet-300 font-bold">{{ $profile->rank_tier }}</span>
                    </div>
                </div>

                @if($profile->bio)
                    <p class="text-xs text-slate-300 italic">"{{ $profile->bio }}"</p>
                @endif

                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800 text-xs">
                    @if($profile->discord_handle)
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            💬 Discord: {{ $profile->discord_handle }}
                        </span>
                    @endif
                    @if($profile->stream_url)
                        <a href="{{ $profile->stream_url }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-fuchsia-500/20 text-fuchsia-300 border border-fuchsia-500/30 hover:underline">
                            📺 Canal de Stream
                        </a>
                    @endif
                </div>
            </div>
        </div>

        
        <div class="cyber-card rounded-3xl p-6">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">LobbyMatch</span>
            <h2 class="font-gaming text-base font-bold text-white mt-0.5 mb-3">VALORAR DEPORTIVIDAD / FAIR PLAY</h2>
            <p class="text-xs text-slate-400 mb-4">
                Califica el desempeño y trabajo en equipo de otros jugadores tras tus partidas para alimentar su historial público.
            </p>

            <form action="{{ route('profile.review') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Seleccionar Jugador Evaluado *</label>
                    <select name="reviewed_user_id" required class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                        @foreach($otherPlayers as $player)
                            <option value="{{ $player->id }}">
                                {{ $player->gamerProfile->gamertag ?? $player->name }} ({{ $player->gamerProfile->favorite_game ?? 'Gamer' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Calificación *</label>
                        <select name="rating" required class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-amber-300 font-bold">
                            <option value="5">★★★★★ (5 - Excelente)</option>
                            <option value="4">★★★★☆ (4 - Muy Bueno)</option>
                            <option value="3">★★★☆☆ (3 - Regular)</option>
                            <option value="2">★★☆☆☆ (2 - Bajo)</option>
                            <option value="1">★☆☆☆☆ (1 - Tóxico/AFK)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Insignia / Reconocimiento *</label>
                        <select name="badge" required class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                            <option value="GG / Buen Compañero">GG / Buen Compañero</option>
                            <option value="Líder Estratégico (IGL)">Líder Estratégico (IGL)</option>
                            <option value="MVP de la Partida">MVP de la Partida</option>
                            <option value="Comunicación Impecable">Comunicación Impecable</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Reseña Post-Partida *</label>
                    <input type="text" name="comment" required
                           placeholder="Ej. Excelente comunicación en la semifinal de Valorant, muy recomendado."
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl font-bold bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 transition">
                    ⭐ Enviar Valoración de Reputación
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
