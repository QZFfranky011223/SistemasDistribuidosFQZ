@extends('layouts.app')

@section('title', $user->isAdmin() ? 'Panel de Administrador — LobbyMatch' : 'Zona de Jugador — LobbyMatch')

@section('content')

@if($user->isAdmin())
<div class="space-y-8">
    <div class="cyber-card rounded-3xl p-6 sm:p-8 border-2 border-amber-500/50 bg-gradient-to-br from-amber-500/15 via-[#0f1626] to-[#090d18] flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center font-gaming font-black text-2xl text-slate-950 shadow-xl shadow-amber-500/30">
                ADM
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-0.5 rounded-full text-xs font-black uppercase bg-amber-500 text-slate-950">
                        ⚡ MODO ADMINISTRADOR
                    </span>
                    <h1 class="font-gaming text-2xl sm:text-3xl font-bold text-white">
                        {{ $user->name }}
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-amber-200/80 mt-1">
                    Supervisión en Tiempo Real de la Partida del Jugador (5 Min), Squads y Torneos
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('tournaments.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-lg shadow-amber-500/25 transition">
                ➕ Crear / Gestionar Torneos
            </a>
            <a href="{{ route('squads.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                🛡️ Ver Todas las Squads
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="cyber-card rounded-2xl p-4 border border-emerald-500/30 flex items-center justify-between">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Usuarios Conectados</span>
                <div class="flex items-center gap-2 mt-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span id="liveOnlineUsersCount" class="font-gaming text-2xl font-black text-emerald-400">{{ $platformStats['total_users'] + 14 }}</span>
                    <span class="text-xs text-slate-400">en línea</span>
                </div>
            </div>
            <span class="text-2xl">🟢</span>
        </div>

        <div class="cyber-card rounded-2xl p-4 border border-cyan-500/30 flex items-center justify-between">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Squads Conectadas</span>
                <div class="flex items-center gap-2 mt-1">
                    <span id="liveConnectedSquadsCount" class="font-gaming text-2xl font-black text-cyan-300">{{ $allSquads->count() }}</span>
                    <span class="text-xs text-cyan-400 font-semibold">Squads activas</span>
                </div>
            </div>
            <span class="text-2xl">🛡️</span>
        </div>

        <div class="cyber-card rounded-2xl p-4 border border-amber-500/30 flex items-center justify-between">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Duración Transmisión</span>
                <div class="flex items-center gap-2 mt-1">
                    <span id="liveMatchDuration" class="font-gaming text-2xl font-black text-amber-300">05:00</span>
                </div>
            </div>
            <span class="text-2xl">⏱️</span>
        </div>

        <div class="cyber-card rounded-2xl p-4 border border-violet-500/30 flex items-center justify-between">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold block">Progreso de Partida</span>
                <div class="flex items-center gap-2 mt-1">
                    <span id="liveMatchProgressText" class="font-gaming text-2xl font-black text-violet-300">0%</span>
                    <span id="liveViewersCount" class="text-xs text-rose-400 font-semibold">👁️ 48 viendo</span>
                </div>
            </div>
            <span class="text-2xl">📡</span>
        </div>
    </div>

    <div class="cyber-card rounded-3xl p-6 sm:p-8 border border-amber-500/40 bg-gradient-to-br from-amber-500/10 via-[#0f1626] to-[#090d18]">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    TRANSMISIÓN EN DIRECTO DEL JUGADOR ACTIVO
                </div>
                <h2 class="font-gaming text-xl sm:text-2xl font-black text-white mt-2">
                    VISOR EN VIVO DE LA PARTIDA DEL USUARIO (5 MINUTOS)
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    Aquí se transmite exactamente el juego que está jugando el usuario en su ventana o celular en tiempo real.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <select id="quickTournamentGame" class="px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm font-semibold focus:outline-none focus:border-amber-400">
                    <option value="Valorant">Valorant (5v5 Tactical)</option>
                    <option value="Fortnite">Fortnite (Battle Royale Squads)</option>
                    <option value="Dota 2">Dota 2 (Captains Mode)</option>
                    <option value="Counter-Strike 2">Counter-Strike 2</option>
                    <option value="CodeRoyale Programación">CodeRoyale (Programación)</option>
                </select>

                <button type="button" id="btnStartQuickTournament" onclick="runLiveTournament()"
                        class="px-6 py-3 rounded-xl font-gaming font-bold text-sm bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 shadow-xl shadow-amber-500/30 transition cursor-pointer">
                    ▶ Sortear Llaves de Torneo
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
            <div class="lg:col-span-8 space-y-4">
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-4 shadow-2xl">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span id="adminLivePill" class="px-2.5 py-1 rounded bg-rose-600 text-white font-bold uppercase tracking-wider text-[10px] animate-pulse">
                                ● EN VIVO
                            </span>
                            <span class="text-slate-400">Jugador transmitiendo:</span>
                            <strong id="streamPlayerName" class="font-gaming text-sm text-cyan-300">Esperando jugador...</strong>
                            <span id="streamSquadBadge" class="px-2 py-0.5 rounded bg-violet-500/20 text-violet-300 border border-violet-500/30 font-bold text-[10px]">
                                SOLO
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400">
                            <span> Estado: <strong id="streamStatusLabel" class="text-emerald-400">Conectado</strong></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                            <span class="text-[11px] text-slate-400 block">Puntos Jugador</span>
                            <span id="adminMirrorScore" class="font-gaming text-2xl font-black text-emerald-400">0</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                            <span class="text-[11px] text-slate-400 block">Reloj (5 Min)</span>
                            <span id="adminMirrorTimer" class="font-gaming text-2xl font-black text-amber-300">05:00</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                            <span class="text-[11px] text-slate-400 block">Kills / Drones</span>
                            <span id="adminMirrorKills" class="font-gaming text-2xl font-black text-rose-400">0</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                            <span class="text-[11px] text-slate-400 block">Salud (HP)</span>
                            <span id="adminMirrorHp" class="font-gaming text-2xl font-black text-cyan-300">100%</span>
                        </div>
                        <div class="col-span-2 sm:col-span-1 p-3 rounded-xl bg-slate-900 border border-slate-800">
                            <span class="text-[11px] text-slate-400 block">Transmisión</span>
                            <span id="adminMirrorState" class="font-gaming text-xs font-bold text-rose-400 block mt-1.5">🔴 EN VIVO</span>
                        </div>
                    </div>

                    <div class="relative rounded-2xl overflow-hidden border-2 border-slate-700 bg-[#050811]">
                        <canvas id="liveGameCanvas" width="720" height="320" class="w-full h-72 sm:h-80 block"></canvas>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs">
                            <span id="streamActionTicker" class="text-slate-200 font-medium">
                                📡 Esperando acciones en vivo del jugador...
                            </span>
                            <span id="liveTourneyProgressLabel" class="text-cyan-300 font-bold">0% (00:00 / 05:00)</span>
                        </div>
                        <div class="w-full bg-slate-900 rounded-full h-3 overflow-hidden border border-slate-800">
                            <div id="liveTourneyProgress" class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-violet-500 h-3 transition-all duration-200" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-4">
                <div class="p-4 rounded-2xl bg-slate-900/95 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span id="liveTourneyTitle" class="font-gaming font-bold text-xs text-white">LLAVES DEL TORNEO</span>
                        <span id="liveTourneyStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            ● Activo
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1 text-xs">
                            <div class="flex justify-between text-[10px] text-violet-300 font-gaming font-bold">
                                <span>SEMIFINAL 1</span>
                                <span id="m0Winner" class="text-emerald-400">Lista</span>
                            </div>
                            <div class="flex justify-between"><span id="m0SquadA" class="text-white font-semibold">[USFX] USFX Titans</span><span id="m0ScoreA" class="text-amber-300 font-bold">2</span></div>
                            <div class="flex justify-between"><span id="m0SquadB" class="text-white font-semibold">[SVPR] Sucre Vipers</span><span id="m0ScoreB" class="text-amber-300 font-bold">1</span></div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1 text-xs">
                            <div class="flex justify-between text-[10px] text-cyan-300 font-gaming font-bold">
                                <span>SEMIFINAL 2</span>
                                <span id="m01Winner" class="text-emerald-400">Lista</span>
                            </div>
                            <div class="flex justify-between"><span id="m1SquadA" class="text-white font-semibold">[ANDN] Andean Immortals</span><span id="m1ScoreA" class="text-amber-300 font-bold">2</span></div>
                            <div class="flex justify-between"><span id="m1SquadB" class="text-white font-semibold">[SQ4] Charcas Wolves</span><span id="m1ScoreB" class="text-amber-300 font-bold">0</span></div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-950 border border-amber-500/40 space-y-1 text-xs">
                            <div class="flex justify-between text-[10px] text-amber-300 font-gaming font-bold">
                                <span>🏆 GRAN FINAL</span>
                                <span id="m2Winner" class="text-amber-300">En curso</span>
                            </div>
                            <div class="flex justify-between"><span id="m2SquadA" class="text-white font-semibold">[USFX] USFX Titans</span><span id="m2ScoreA" class="text-amber-300 font-bold">1</span></div>
                            <div class="flex justify-between"><span id="m2SquadB" class="text-white font-semibold">[ANDN] Andean Immortals</span><span id="m2ScoreB" class="text-amber-300 font-bold">1</span></div>
                        </div>
                    </div>

                    <div id="championBanner" class="hidden p-3 rounded-xl bg-amber-500/20 border border-amber-400 text-center">
                        <div id="championSquadName" class="font-gaming text-sm font-black text-white"></div>
                        <p id="championSquadStats" class="text-[11px] text-emerald-300"></p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900/95 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h3 class="font-gaming text-xs font-bold text-cyan-300 uppercase">🛡️ Squads Conectadas ({{ $allSquads->count() }})</h3>
                        <span class="text-[11px] text-emerald-400 font-semibold">● Online</span>
                    </div>
                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                        @foreach($allSquads as $idx => $sq)
                            <div class="p-2 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span class="px-1.5 py-0.5 rounded font-gaming font-bold text-[10px] text-white" style="background-color: {{ $sq->logo_color }}">{{ $sq->tag }}</span>
                                    <span class="font-semibold text-white truncate max-w-[120px]">{{ $sq->name }}</span>
                                </div>
                                <span class="text-[11px] text-emerald-300">{{ $sq->active_members_count }} jug.</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900/95 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="font-gaming text-xs font-bold text-emerald-300 uppercase">🟢 Usuarios Conectados ({{ $onlineUsers->count() }})</h3>
                    </div>
                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                        @foreach($onlineUsers as $ou)
                            <div class="p-2 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                                <span class="font-semibold text-white">{{ $ou->gamerProfile->gamertag ?? $ou->name }}</span>
                                <span class="text-[11px] text-slate-400">{{ $ou->gamerProfile->platform ?? 'PC' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@else

<div class="space-y-8">
    <div class="cyber-card rounded-3xl p-6 sm:p-8 border-2 border-cyan-500/40 bg-gradient-to-br from-violet-600/15 via-[#0f1626] to-[#090d18] flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 to-violet-600 flex items-center justify-center font-gaming font-black text-2xl text-white shadow-xl shadow-cyan-500/30">
                {{ strtoupper(substr($user->gamerProfile->gamertag ?? $user->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-0.5 rounded-full text-xs font-black uppercase bg-cyan-400 text-slate-950">
                        🎮 MODO JUGADOR {{ $user->isCaptain() ? '(CAPITÁN DE SQUAD)' : '' }}
                    </span>
                    <h1 class="font-gaming text-2xl sm:text-3xl font-bold text-white">
                        {{ $user->gamerProfile->gamertag ?? $user->name }}
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-violet-500/20 text-violet-300 border border-violet-500/40">
                        {{ $user->gamerProfile->rank_tier ?? 'Oro I' }} (Nivel <span id="playerDashLevel">{{ $user->gamerProfile->level ?? 1 }}</span>)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    Juego: <strong class="text-cyan-300">{{ $user->gamerProfile->favorite_game }}</strong>
                    · ID: <code class="text-emerald-300 font-semibold">{{ $user->gamerProfile->game_id }}</code>
                    · Plataforma: <strong>{{ $user->gamerProfile->platform ?? 'PC' }}</strong>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('squads.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-violet-600 hover:bg-violet-500 text-white transition">
                🛡️ Mi Squad
            </a>
            <a href="{{ route('tournaments.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-cyan-500 hover:bg-cyan-400 text-slate-950 transition">
                🏆 Inscribirse a Torneo
            </a>
            <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                ⚙️ Mi Perfil
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 cyber-card rounded-3xl p-6 sm:p-8 border-2 border-emerald-500/40 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">
                            🔴 TRANSMITIENDO EN VIVO AL ADMINISTRADOR
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            ⏱️ 5 MINUTOS (05:00)
                        </span>
                    </div>
                    <h2 class="font-gaming text-xl sm:text-2xl font-black text-white mt-2">
                        PARTIDA EN VIVO DE 5 MINUTOS
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        Muévete con <strong>W A S D / Flechas / Mouse</strong> o deslizando el dedo en celular. Dispara con <strong>ESPACIO / CLIC / 🔥 LÁSER</strong>.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btnStartMiniGame" onclick="startPlayerGame()"
                            class="px-5 py-3 rounded-2xl font-gaming font-black text-xs sm:text-sm bg-gradient-to-r from-emerald-400 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 text-slate-950 shadow-xl shadow-emerald-500/30 transition cursor-pointer whitespace-nowrap">
                        ▶ INICIAR PARTIDA (05:00)
                    </button>
                    <button type="button" id="btnFinishEarly" onclick="endPlayerGame()"
                            class="hidden px-4 py-3 rounded-2xl font-bold text-xs bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 transition cursor-pointer whitespace-nowrap">
                        ⏹ Terminar
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[11px] text-slate-400 block">Mis Puntos</span>
                    <span id="gameScoreText" class="font-gaming text-2xl font-black text-emerald-400">0</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[11px] text-slate-400 block">Reloj (5 Min)</span>
                    <span id="gameTimerText" class="font-gaming text-2xl font-black text-amber-300">05:00</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[11px] text-slate-400 block">Kills / Drones</span>
                    <span id="gameKillsText" class="font-gaming text-2xl font-black text-rose-400">0</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[11px] text-slate-400 block">Salud (HP)</span>
                    <span id="gameHpText" class="font-gaming text-2xl font-black text-cyan-300">100%</span>
                </div>
                <div class="col-span-2 sm:col-span-1 p-3 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-[11px] text-slate-400 block">Transmisión</span>
                    <span id="gameTrendText" class="font-gaming text-xs font-bold text-rose-400 block mt-1.5">🔴 EN VIVO</span>
                </div>
            </div>

            <div class="relative rounded-2xl overflow-hidden border-2 border-slate-700 bg-[#050811] touch-none">
                <canvas id="playerGameCanvas" width="720" height="320" class="w-full h-72 sm:h-80 block cursor-crosshair touch-none"></canvas>

                <div id="gameOverlayStart" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center">
                    <div class="text-4xl mb-2">🎮</div>
                    <h3 id="overlayTitle" class="font-gaming text-xl font-black text-white">PARTIDA DE 5 MINUTOS EN VIVO</h3>
                    <p id="overlaySubtitle" class="text-xs sm:text-sm text-slate-300 max-w-md mt-1 mb-5">
                        Al presionar Jugar Ahora, tu partida se transmite en vivo e idéntica a la ventana del Administrador.
                    </p>
                    <button type="button" onclick="startPlayerGame()"
                            class="px-8 py-3.5 rounded-2xl font-gaming font-black text-sm bg-gradient-to-r from-emerald-400 to-cyan-400 text-slate-950 shadow-xl shadow-emerald-500/30">
                        🚀 JUGAR AHORA (5 MINUTOS)
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-2 pt-1">
                <button type="button" ontouchstart="moveTouch(-28,0)" onclick="moveTouch(-28,0)" class="py-3 rounded-xl bg-slate-900 active:bg-cyan-500/30 border border-slate-700 text-white font-bold text-base select-none">⬅️</button>
                <button type="button" ontouchstart="moveTouch(0,-28)" onclick="moveTouch(0,-28)" class="py-3 rounded-xl bg-slate-900 active:bg-cyan-500/30 border border-slate-700 text-white font-bold text-base select-none">⬆️</button>
                <button type="button" ontouchstart="moveTouch(0,28)" onclick="moveTouch(0,28)" class="py-3 rounded-xl bg-slate-900 active:bg-cyan-500/30 border border-slate-700 text-white font-bold text-base select-none">⬇️</button>
                <button type="button" ontouchstart="moveTouch(28,0)" onclick="moveTouch(28,0)" class="py-3 rounded-xl bg-slate-900 active:bg-cyan-500/30 border border-slate-700 text-white font-bold text-base select-none">➡️</button>
                <button type="button" ontouchstart="shootBullet()" onclick="shootBullet()" class="py-3 rounded-xl bg-amber-500 active:bg-amber-400 text-slate-950 font-gaming font-black text-xs select-none">🔥 LÁSER</button>
            </div>

            <div class="space-y-1.5">
                <div class="flex justify-between text-xs">
                    <span id="playerLastActionLabel" class="text-slate-300">Progreso de la Partida de 5 Minutos</span>
                    <span id="playerGameProgressPct" class="text-cyan-300 font-bold">0% (00:00 / 05:00)</span>
                </div>
                <div class="w-full bg-slate-900 rounded-full h-3 overflow-hidden border border-slate-800">
                    <div id="playerGameProgressBar" class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-violet-500 h-3 transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 space-y-6">
            <div class="cyber-card rounded-3xl p-6 border border-cyan-500/30">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-gaming text-base font-bold text-white flex items-center gap-2">
                        <span>🟢</span> Jugadores en Línea ({{ $onlineUsers->count() }})
                    </h2>
                    <span class="text-xs text-emerald-400 font-semibold">● Activos</span>
                </div>
                <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                    @foreach($onlineUsers as $jugador)
                        <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-violet-600 to-cyan-500 flex items-center justify-center font-bold text-xs text-white">
                                    {{ strtoupper(substr($jugador->gamerProfile->gamertag ?? $jugador->name, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-xs text-white block">{{ $jugador->gamerProfile->gamertag ?? $jugador->name }}</span>
                                    <span class="text-[10px] text-slate-400 block">
                                        {{ $jugador->gamerProfile->favorite_game ?? 'Fortnite' }} · {{ $jugador->gamerProfile->platform ?? 'PC' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs font-gaming font-bold text-violet-300">{{ $jugador->gamerProfile->rank_tier ?? 'Oro I' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="cyber-card rounded-3xl p-6">
                <h2 class="font-gaming text-base font-bold text-white mb-3">🛡️ Mi Squad Actual</h2>
                @if($activeSquad)
                    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-white">[{{ $activeSquad->tag }}] {{ $activeSquad->name }}</div>
                            <div class="text-xs text-slate-400">Código: <code class="text-cyan-300 font-bold">{{ $activeSquad->invite_code }}</code></div>
                        </div>
                        <a href="{{ route('squads.show', $activeSquad) }}" class="px-3 py-1.5 rounded-lg bg-violet-600 text-white text-xs font-bold">
                            Ver Sala
                        </a>
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-slate-900/70 border border-dashed border-slate-700 text-center space-y-2">
                        <p class="text-xs text-slate-400">Aún no tienes Squad. Únete con un código o crea el tuyo.</p>
                        <a href="{{ route('squads.index') }}" class="inline-block px-4 py-2 rounded-xl bg-violet-600 text-white text-xs font-bold">
                            Ir a Squads
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if($user->isAdmin())
<script>
    const quickTourneyUrl = '/dashboard/iniciar-torneo';
    const liveStreamEndpoint = '/live-stream.php';
    const dashCsrf = @json(csrf_token());

    const canvas = document.getElementById('liveGameCanvas');
    const ctx = canvas.getContext('2d');
    let remotePlayerState = null;

    function formatClock(sec) {
        const m = String(Math.floor(sec / 60)).padStart(2, '0');
        const s = String(sec % 60).padStart(2, '0');
        return `${m}:${s}`;
    }

    function applyRemoteState(data) {
        if (!data || !data.is_live) return;
        remotePlayerState = data;

        const remainingFmt = formatClock(data.remaining_seconds ?? 300);
        const elapsedFmt = formatClock(data.elapsed_seconds ?? 0);

        document.getElementById('streamPlayerName').textContent = data.gamertag || 'Jugador';
        document.getElementById('streamSquadBadge').textContent = data.squad_tag || 'SOLO';
        document.getElementById('adminMirrorScore').textContent = data.score ?? 0;
        document.getElementById('adminMirrorTimer').textContent = remainingFmt;
        document.getElementById('adminMirrorKills').textContent = data.kills ?? 0;
        document.getElementById('adminMirrorHp').textContent = (data.hp ?? 100) + '%';

        document.getElementById('liveMatchDuration').textContent = remainingFmt;
        document.getElementById('liveMatchProgressText').textContent = (data.progress_pct ?? 0) + '%';
        document.getElementById('liveTourneyProgress').style.width = (data.progress_pct ?? 0) + '%';
        document.getElementById('liveTourneyProgressLabel').textContent = `${data.progress_pct ?? 0}% (${elapsedFmt} / 05:00)`;
        document.getElementById('streamActionTicker').textContent = `💎 ${data.gamertag}: ${data.last_event || 'Jugando en vivo'}`;
    }

    try {
        const bc = new BroadcastChannel('lobbymatch_live_channel');
        bc.onmessage = (ev) => {
            if (ev.data) applyRemoteState(ev.data);
        };
    } catch (e) {}

    window.addEventListener('storage', (ev) => {
        if (ev.key === 'lobbymatch_live_state' && ev.newValue) {
            try {
                applyRemoteState(JSON.parse(ev.newValue));
            } catch (e) {}
        }
    });

    async function pollServerLiveStream() {
        try {
            const res = await fetch(liveStreamEndpoint + '?t=' + Date.now(), { cache: 'no-store' });
            const data = await res.json();
            if (data && data.is_live) {
                applyRemoteState(data);
            }
        } catch (err) {}
    }

    setInterval(pollServerLiveStream, 350);
    pollServerLiveStream();

    function drawLiveArena() {
        ctx.fillStyle = '#050811';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.strokeStyle = 'rgba(34, 211, 238, 0.07)';
        ctx.lineWidth = 1;
        for (let x = 0; x < canvas.width; x += 45) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, canvas.height);
            ctx.stroke();
        }
        for (let y = 0; y < canvas.height; y += 45) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(canvas.width, y);
            ctx.stroke();
        }

        if (remotePlayerState && remotePlayerState.is_live) {
            (remotePlayerState.crystals || []).forEach((c) => {
                ctx.beginPath();
                ctx.arc(c.x, c.y, 9, 0, Math.PI * 2);
                ctx.fillStyle = '#10b981';
                ctx.fill();
                ctx.strokeStyle = '#a7f3d0';
                ctx.lineWidth = 2;
                ctx.stroke();
            });

            (remotePlayerState.enemies || []).forEach((en) => {
                ctx.beginPath();
                ctx.arc(en.x, en.y, 12, 0, Math.PI * 2);
                ctx.fillStyle = '#f43f5e';
                ctx.fill();
                ctx.strokeStyle = '#fecdd3';
                ctx.lineWidth = 2;
                ctx.stroke();
            });

            (remotePlayerState.bullets || []).forEach((b) => {
                ctx.beginPath();
                ctx.arc(b.x, b.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = '#facc15';
                ctx.fill();
            });

            ctx.beginPath();
            ctx.arc(remotePlayerState.player_x, remotePlayerState.player_y, 14, 0, Math.PI * 2);
            ctx.fillStyle = '#06b6d4';
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 3;
            ctx.stroke();

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 11px Inter';
            ctx.fillText(remotePlayerState.gamertag || 'Jugador', remotePlayerState.player_x - 28, remotePlayerState.player_y - 20);
        } else {
            ctx.fillStyle = '#94a3b8';
            ctx.font = 'bold 14px Inter';
            ctx.textAlign = 'center';
            ctx.fillText('Esperando a que un Jugador inicie su partida de 5 minutos...', canvas.width / 2, canvas.height / 2);
            ctx.textAlign = 'start';
        }

        requestAnimationFrame(drawLiveArena);
    }
    drawLiveArena();

    async function runLiveTournament() {
        const btn = document.getElementById('btnStartQuickTournament');
        const game = document.getElementById('quickTournamentGame').value;
        btn.disabled = true;
        btn.textContent = '⏳ Sorteando...';

        try {
            const response = await fetch(quickTourneyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': dashCsrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ game })
            });
            const data = await response.json();
            const r0 = data.rounds[0];
            const r1 = data.rounds[1];
            const r2 = data.rounds[2];

            document.getElementById('m0SquadA').textContent = `[${r0.squad_a.tag}] ${r0.squad_a.name}`;
            document.getElementById('m0SquadB').textContent = `[${r0.squad_b.tag}] ${r0.squad_b.name}`;
            document.getElementById('m0ScoreA').textContent = r0.score_a;
            document.getElementById('m0ScoreB').textContent = r0.score_b;
            document.getElementById('m0Winner').textContent = `Gana [${r0.winner.tag}]`;

            document.getElementById('m1SquadA').textContent = `[${r1.squad_a.tag}] ${r1.squad_a.name}`;
            document.getElementById('m1SquadB').textContent = `[${r1.squad_b.tag}] ${r1.squad_b.name}`;
            document.getElementById('m1ScoreA').textContent = r1.score_a;
            document.getElementById('m1ScoreB').textContent = r1.score_b;
            document.getElementById('m01Winner').textContent = `Gana [${r1.winner.tag}]`;

            document.getElementById('m2SquadA').textContent = `[${r2.squad_a.tag}] ${r2.squad_a.name}`;
            document.getElementById('m2SquadB').textContent = `[${r2.squad_b.tag}] ${r2.squad_b.name}`;
            document.getElementById('m2ScoreA').textContent = r2.score_a;
            document.getElementById('m2ScoreB').textContent = r2.score_b;
            document.getElementById('m2Winner').textContent = `🏆 [${r2.winner.tag}]`;

            const champBanner = document.getElementById('championBanner');
            champBanner.classList.remove('hidden');
            document.getElementById('championSquadName').textContent = `🏆 [${data.champion.tag}] ${data.champion.name}`;
            document.getElementById('championSquadStats').textContent = `Campeón (${r2.score_a} - ${r2.score_b})`;
        } catch (err) {
            console.error(err);
        } finally {
            btn.disabled = false;
            btn.textContent = '▶ Sortear Llaves de Torneo';
        }
    }
</script>
@else
<script>
    const submitScoreUrl = '/arena/submit';
    const liveStreamEndpoint = '/live-stream.php';
    const playerCsrf = @json(csrf_token());
    const currentGamertag = @json($user->gamerProfile->gamertag ?? $user->name);
    const currentSquadTag = @json($activeSquad ? $activeSquad->tag : 'SOLO');

    const pCanvas = document.getElementById('playerGameCanvas');
    const pCtx = pCanvas.getContext('2d');

    let liveBc = null;
    try {
        liveBc = new BroadcastChannel('lobbymatch_live_channel');
    } catch (e) {}

    const TOTAL_GAME_SECONDS = 300;
    let gameRunning = false;
    let score = 0;
    let rivalScore = 0;
    let kills = 0;
    let crystalsCollected = 0;
    let hp = 100;
    let timeLeft = TOTAL_GAME_SECONDS;
    let lastEventText = 'Partida en curso';
    let timerInterval = null;
    let serverSyncInterval = null;

    const player = { x: 360, y: 160, r: 14, speed: 5, dirX: 1, dirY: 0 };
    const keys = {};
    let crystals = [];
    let enemies = [];
    let bullets = [];

    function formatClock(sec) {
        const m = String(Math.floor(sec / 60)).padStart(2, '0');
        const s = String(sec % 60).padStart(2, '0');
        return `${m}:${s}`;
    }

    function buildStatePayload(isLiveFlag = true) {
        const elapsed = TOTAL_GAME_SECONDS - timeLeft;
        const pct = Math.min(100, Math.round((elapsed / TOTAL_GAME_SECONDS) * 100));
        return {
            is_live: isLiveFlag,
            gamertag: currentGamertag,
            squad_tag: currentSquadTag,
            score,
            rival_score: rivalScore,
            kills,
            crystals_count: crystalsCollected,
            hp,
            elapsed_seconds: elapsed,
            remaining_seconds: timeLeft,
            progress_pct: pct,
            player_x: Math.round(player.x),
            player_y: Math.round(player.y),
            crystals: crystals.map(c => ({ x: Math.round(c.x), y: Math.round(c.y) })),
            enemies: enemies.map(en => ({ x: Math.round(en.x), y: Math.round(en.y) })),
            bullets: bullets.slice(0, 10).map(b => ({ x: Math.round(b.x), y: Math.round(b.y) })),
            last_event: lastEventText
        };
    }

    function broadcastFrameInstant() {
        if (!gameRunning) return;
        const payload = buildStatePayload(true);
        if (liveBc) {
            try { liveBc.postMessage(payload); } catch (e) {}
        }
        try {
            localStorage.setItem('lobbymatch_live_state', JSON.stringify(payload));
        } catch (e) {}
    }

    async function syncLiveStateToServer(isLiveFlag = true) {
        const payload = buildStatePayload(isLiveFlag);
        try {
            await fetch(liveStreamEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
        } catch (err) {}
    }

    function shootBullet(targetX = null, targetY = null) {
        if (!gameRunning) return;
        let vx = player.dirX * 9;
        let vy = player.dirY * 9;
        if (targetX !== null && targetY !== null) {
            const angle = Math.atan2(targetY - player.y, targetX - player.x);
            vx = Math.cos(angle) * 9;
            vy = Math.sin(angle) * 9;
        }
        if (vx === 0 && vy === 0) vx = 9;
        bullets.push({ x: player.x, y: player.y, vx, vy, r: 4 });
    }

    window.addEventListener('keydown', (e) => {
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', ' '].includes(e.key) && gameRunning) {
            e.preventDefault();
        }
        const k = e.key.toLowerCase();
        keys[k] = true;
        if (e.key === ' ' && gameRunning) {
            shootBullet();
        }
    });

    window.addEventListener('keyup', (e) => {
        keys[e.key.toLowerCase()] = false;
    });

    pCanvas.addEventListener('mousemove', (e) => {
        if (!gameRunning) return;
        const rect = pCanvas.getBoundingClientRect();
        const scaleX = pCanvas.width / rect.width;
        const scaleY = pCanvas.height / rect.height;
        const nx = (e.clientX - rect.left) * scaleX;
        const ny = (e.clientY - rect.top) * scaleY;
        const angle = Math.atan2(ny - player.y, nx - player.x);
        player.dirX = Math.cos(angle);
        player.dirY = Math.sin(angle);
        player.x = nx;
        player.y = ny;
    });

    pCanvas.addEventListener('click', (e) => {
        if (!gameRunning) return;
        const rect = pCanvas.getBoundingClientRect();
        const scaleX = pCanvas.width / rect.width;
        const scaleY = pCanvas.height / rect.height;
        shootBullet((e.clientX - rect.left) * scaleX, (e.clientY - rect.top) * scaleY);
    });

    function handleTouch(e) {
        if (!gameRunning || !e.touches || !e.touches[0]) return;
        e.preventDefault();
        const t = e.touches[0];
        const rect = pCanvas.getBoundingClientRect();
        const scaleX = pCanvas.width / rect.width;
        const scaleY = pCanvas.height / rect.height;
        const nx = (t.clientX - rect.left) * scaleX;
        const ny = (t.clientY - rect.top) * scaleY;
        const angle = Math.atan2(ny - player.y, nx - player.x);
        player.dirX = Math.cos(angle);
        player.dirY = Math.sin(angle);
        player.x = Math.max(player.r, Math.min(pCanvas.width - player.r, nx));
        player.y = Math.max(player.r, Math.min(pCanvas.height - player.r, ny));
        shootBullet(nx + player.dirX * 40, ny + player.dirY * 40);
    }

    pCanvas.addEventListener('touchstart', handleTouch, { passive: false });
    pCanvas.addEventListener('touchmove', handleTouch, { passive: false });

    function moveTouch(dx, dy) {
        if (!gameRunning) return;
        player.x = Math.max(player.r, Math.min(pCanvas.width - player.r, player.x + dx));
        player.y = Math.max(player.r, Math.min(pCanvas.height - player.r, player.y + dy));
        if (dx !== 0 || dy !== 0) {
            const angle = Math.atan2(dy, dx);
            player.dirX = Math.cos(angle);
            player.dirY = Math.sin(angle);
        }
    }

    function spawnEntities() {
        crystals = [];
        enemies = [];
        bullets = [];
        for (let i = 0; i < 6; i++) {
            crystals.push({
                x: 40 + Math.random() * (pCanvas.width - 80),
                y: 40 + Math.random() * (pCanvas.height - 80),
                r: 9
            });
        }
        for (let i = 0; i < 5; i++) {
            enemies.push({
                x: 50 + Math.random() * (pCanvas.width - 100),
                y: 50 + Math.random() * (pCanvas.height - 100),
                vx: (Math.random() > 0.5 ? 1 : -1) * (1.8 + Math.random() * 1.4),
                vy: (Math.random() > 0.5 ? 1 : -1) * (1.8 + Math.random() * 1.4),
                r: 12
            });
        }
    }

    function startPlayerGame() {
        score = 0;
        rivalScore = 0;
        kills = 0;
        crystalsCollected = 0;
        hp = 100;
        timeLeft = TOTAL_GAME_SECONDS;
        gameRunning = true;
        lastEventText = 'Inició partida en vivo de 5 minutos';

        document.getElementById('gameScoreText').textContent = '0';
        document.getElementById('gameKillsText').textContent = '0';
        document.getElementById('gameHpText').textContent = '100%';
        document.getElementById('gameTimerText').textContent = '05:00';
        document.getElementById('gameOverlayStart').classList.add('hidden');
        document.getElementById('btnFinishEarly').classList.remove('hidden');
        document.getElementById('playerGameProgressBar').style.width = '0%';
        document.getElementById('playerGameProgressPct').textContent = '0% (00:00 / 05:00)';

        spawnEntities();
        broadcastFrameInstant();
        syncLiveStateToServer(true);

        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timeLeft--;
            const elapsed = TOTAL_GAME_SECONDS - timeLeft;
            const pct = Math.min(100, Math.round((elapsed / TOTAL_GAME_SECONDS) * 100));
            document.getElementById('gameTimerText').textContent = formatClock(timeLeft);
            document.getElementById('playerGameProgressBar').style.width = pct + '%';
            document.getElementById('playerGameProgressPct').textContent = `${pct}% (${formatClock(elapsed)} / 05:00)`;

            if (timeLeft <= 0) {
                endPlayerGame();
            }
        }, 1000);

        clearInterval(serverSyncInterval);
        serverSyncInterval = setInterval(() => {
            if (gameRunning) syncLiveStateToServer(true);
        }, 300);
    }

    async function endPlayerGame() {
        gameRunning = false;
        clearInterval(timerInterval);
        clearInterval(serverSyncInterval);
        document.getElementById('btnFinishEarly').classList.add('hidden');

        lastEventText = `Partida finalizada (${score} pts · ${kills} Kills)`;
        await syncLiveStateToServer(false);

        const overlay = document.getElementById('gameOverlayStart');
        overlay.classList.remove('hidden');

        const won = score >= 40;
        document.getElementById('overlayTitle').textContent = won
            ? `🏆 ¡VICTORIA! (${score} PTS · ${kills} KILLS)`
            : `PARTIDA TERMINADA (${score} PTS · ${kills} KILLS)`;

        try {
            const res = await fetch(submitScoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': playerCsrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    mode: 'aim_reflex',
                    score: Math.min(1000, Math.max(10, score)),
                    accuracy: won ? 85 : 50
                })
            });
            const data = await res.json();
            if (data.level) {
                document.getElementById('playerDashLevel').textContent = data.level;
            }
        } catch (e) {}
    }

    function updateAndDrawPlayerGame() {
        pCtx.fillStyle = '#050811';
        pCtx.fillRect(0, 0, pCanvas.width, pCanvas.height);

        pCtx.strokeStyle = 'rgba(34, 211, 238, 0.07)';
        for (let x = 0; x < pCanvas.width; x += 45) {
            pCtx.beginPath();
            pCtx.moveTo(x, 0);
            pCtx.lineTo(x, pCanvas.height);
            pCtx.stroke();
        }
        for (let y = 0; y < pCanvas.height; y += 45) {
            pCtx.beginPath();
            pCtx.moveTo(0, y);
            pCtx.lineTo(pCanvas.width, y);
            pCtx.stroke();
        }

        if (gameRunning) {
            if (keys['arrowup'] || keys['w']) { player.y -= player.speed; player.dirY = -1; player.dirX = 0; }
            if (keys['arrowdown'] || keys['s']) { player.y += player.speed; player.dirY = 1; player.dirX = 0; }
            if (keys['arrowleft'] || keys['a']) { player.x -= player.speed; player.dirX = -1; player.dirY = 0; }
            if (keys['arrowright'] || keys['d']) { player.x += player.speed; player.dirX = 1; player.dirY = 0; }

            player.x = Math.max(player.r, Math.min(pCanvas.width - player.r, player.x));
            player.y = Math.max(player.r, Math.min(pCanvas.height - player.r, player.y));

            for (let i = bullets.length - 1; i >= 0; i--) {
                const b = bullets[i];
                b.x += b.vx;
                b.y += b.vy;
                if (b.x < 0 || b.x > pCanvas.width || b.y < 0 || b.y > pCanvas.height) {
                    bullets.splice(i, 1);
                    continue;
                }
                for (let j = 0; j < enemies.length; j++) {
                    const en = enemies[j];
                    if (Math.hypot(b.x - en.x, b.y - en.y) < b.r + en.r) {
                        score += 25;
                        kills++;
                        lastEventText = `Eliminó dron enemigo (+25 pts · ${kills} Kills)`;
                        document.getElementById('gameScoreText').textContent = score;
                        document.getElementById('gameKillsText').textContent = kills;
                        document.getElementById('playerLastActionLabel').textContent = '🎯 ' + lastEventText;
                        en.x = 40 + Math.random() * (pCanvas.width - 80);
                        en.y = 40 + Math.random() * (pCanvas.height - 80);
                        bullets.splice(i, 1);
                        break;
                    }
                }
            }

            crystals.forEach((c) => {
                if (Math.hypot(player.x - c.x, player.y - c.y) < player.r + c.r) {
                    score += 10;
                    crystalsCollected++;
                    hp = Math.min(100, hp + 4);
                    lastEventText = `Capturó cristal de energía (+10 pts)`;
                    c.x = 35 + Math.random() * (pCanvas.width - 70);
                    c.y = 35 + Math.random() * (pCanvas.height - 70);
                    document.getElementById('gameScoreText').textContent = score;
                    document.getElementById('gameHpText').textContent = hp + '%';
                    document.getElementById('playerLastActionLabel').textContent = '💎 ' + lastEventText;
                }
            });

            enemies.forEach((en) => {
                en.x += en.vx;
                en.y += en.vy;
                if (en.x < en.r || en.x > pCanvas.width - en.r) en.vx *= -1;
                if (en.y < en.r || en.y > pCanvas.height - en.r) en.vy *= -1;

                if (Math.hypot(player.x - en.x, player.y - en.y) < player.r + en.r) {
                    score = Math.max(0, score - 10);
                    rivalScore += 15;
                    hp = Math.max(10, hp - 8);
                    lastEventText = `Recibió impacto de dron rival (-10 pts)`;
                    en.x = 40 + Math.random() * (pCanvas.width - 80);
                    en.y = 40 + Math.random() * (pCanvas.height - 80);
                    document.getElementById('gameScoreText').textContent = score;
                    document.getElementById('gameHpText').textContent = hp + '%';
                }
            });

            broadcastFrameInstant();
        }

        crystals.forEach((c) => {
            pCtx.beginPath();
            pCtx.arc(c.x, c.y, c.r, 0, Math.PI * 2);
            pCtx.fillStyle = '#10b981';
            pCtx.fill();
            pCtx.strokeStyle = '#a7f3d0';
            pCtx.lineWidth = 2;
            pCtx.stroke();
        });

        enemies.forEach((en) => {
            pCtx.beginPath();
            pCtx.arc(en.x, en.y, en.r, 0, Math.PI * 2);
            pCtx.fillStyle = '#f43f5e';
            pCtx.fill();
            pCtx.strokeStyle = '#fecdd3';
            pCtx.lineWidth = 2;
            pCtx.stroke();
        });

        bullets.forEach((b) => {
            pCtx.beginPath();
            pCtx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
            pCtx.fillStyle = '#facc15';
            pCtx.fill();
        });

        pCtx.beginPath();
        pCtx.arc(player.x, player.y, player.r, 0, Math.PI * 2);
        pCtx.fillStyle = '#06b6d4';
        pCtx.fill();
        pCtx.strokeStyle = '#ffffff';
        pCtx.lineWidth = 3;
        pCtx.stroke();

        requestAnimationFrame(updateAndDrawPlayerGame);
    }

    spawnEntities();
    updateAndDrawPlayerGame();
</script>
@endif

@endsection
