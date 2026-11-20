@extends('layouts.app')

@section('title', 'Arena en Tiempo Real & Test de Evolución — LobbyMatch')

@section('content')
<div class="space-y-8">

    
    <div class="cyber-card rounded-3xl p-6 sm:p-8 border border-emerald-500/40 relative overflow-hidden">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    SISTEMA EN TIEMPO REAL · MONITOR DE SQUADS & ARENA SOLITARIA
                </div>
                <h1 class="font-gaming text-2xl sm:text-3xl font-black text-white mt-2">
                    ARENA DE ENTRENAMIENTO, PROGRAMACIÓN Y RADAR EN VIVO
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Pon a prueba tu nivel con retos de <strong>Programación (CodeRoyale)</strong>, <strong>Reflejos eSports</strong> o <strong>Táctica IGL</strong> y observa en tiempo real cómo mejoras o empeoras junto a la actividad de todas las Squads.
                </p>
            </div>

            
            <div id="playerTrendBadge" class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-right min-w-[250px]">
                <span class="text-[11px] uppercase tracking-wider text-slate-300 block">Estado de Rendimiento Actual</span>
                <div id="playerTrendTitle" class="font-gaming font-black text-lg text-emerald-300 mt-0.5">
                    📈 RENDIMIENTO ÓPTIMO
                </div>
                <span id="playerTrendSub" class="text-xs text-slate-300">
                    Completa un test abajo para medir tu evolución
                </span>
            </div>
        </div>

        
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-6 pt-6 border-t border-slate-800/80">
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
                <span class="text-xs text-slate-400 block">Nivel / MMR</span>
                <div id="statLevel" class="font-gaming text-2xl font-black text-cyan-300 mt-1">{{ $user->gamerProfile->level ?? 1 }}</div>
                <span id="statDelta" class="text-[11px] text-emerald-400 font-semibold">En tiempo real</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
                <span class="text-xs text-slate-400 block">Rango Competitivo</span>
                <div id="statRank" class="font-gaming text-lg font-bold text-violet-300 mt-1">{{ $user->gamerProfile->rank_tier ?? 'Oro I' }}</div>
                <span class="text-[11px] text-slate-500">Sube o baja según tus tests</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
                <span class="text-xs text-slate-400 block">Victorias / Tests Aprobados</span>
                <div id="statWins" class="font-gaming text-2xl font-black text-emerald-400 mt-1">{{ $user->gamerProfile->wins ?? 0 }}</div>
                <span class="text-[11px] text-slate-500">De <span id="statPlayed">{{ $user->gamerProfile->matches_played ?? 0 }}</span> jugados</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
                <span class="text-xs text-slate-400 block">Efectividad (Win Rate)</span>
                <div id="statWinRate" class="font-gaming text-2xl font-black text-amber-300 mt-1">{{ $user->gamerProfile->win_rate ?? 0 }}%</div>
                <span class="text-[11px] text-slate-500">Mínimo 60% para subir</span>
            </div>
            <div class="col-span-2 sm:col-span-1 p-4 rounded-2xl bg-slate-900/90 border border-slate-800 flex flex-col justify-between">
                <span class="text-xs text-slate-400 block">Curva de Evolución (Historial)</span>
                <div id="historyBars" class="flex items-end gap-1.5 h-10 mt-2">
                    <div class="flex-1 bg-emerald-500/70 rounded-t h-6" title="60%"></div>
                    <div class="flex-1 bg-emerald-500/80 rounded-t h-7" title="70%"></div>
                    <div class="flex-1 bg-rose-500/70 rounded-t h-4" title="40%"></div>
                    <div class="flex-1 bg-emerald-500/90 rounded-t h-8" title="80%"></div>
                    <div class="flex-1 bg-cyan-400 rounded-t h-9" title="90%"></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        
        <div class="lg:col-span-7 space-y-6">
            <div class="cyber-card rounded-3xl p-6 border border-violet-500/30">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-cyan-400">Modo Solitario & Aporte al Squad</span>
                        <h2 class="font-gaming text-xl font-bold text-white">SIMULADOR Y TESTS DE NIVEL EN VIVO</h2>
                    </div>

                    
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="switchMode('code')" id="tabBtnCode"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-violet-600 text-white transition">
                            💻 1. CodeRoyale (Programación)
                        </button>
                        <button type="button" onclick="switchMode('aim')" id="tabBtnAim"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                            🎯 2. Test Reflejos eSports
                        </button>
                        <button type="button" onclick="switchMode('tactical')" id="tabBtnTactical"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                            🧠 3. Decisiones IGL
                        </button>
                    </div>
                </div>

                
                <div id="modeCodePanel" class="space-y-5">
                    <div class="p-4 rounded-2xl bg-slate-900/90 border border-violet-500/30 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-white text-sm">💻 CodeRoyale: Batalla Rápida de Programación y Lógica</h3>
                            <p class="text-xs text-slate-400">Responde correctamente las preguntas de código y bases de datos. Si logras 60% o más, <strong class="text-emerald-300">subes de nivel 📈</strong>; si fallas, <strong class="text-rose-300">tu rango baja 📉</strong>.</p>
                        </div>
                        <span id="codeQuestionCounter" class="px-3 py-1 rounded-lg bg-violet-500/20 text-violet-300 font-gaming font-bold text-xs">
                            Reto 1 / 4
                        </span>
                    </div>

                    <div id="codeQuizContainer" class="p-6 rounded-2xl bg-slate-950/90 border border-slate-800 space-y-4">
                        <div id="codeQuestionText" class="font-bold text-base text-white"></div>
                        <pre id="codeSnippetBox" class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 text-cyan-300 text-xs overflow-x-auto font-mono"></pre>
                        <div id="codeOptionsList" class="grid grid-cols-1 gap-2.5"></div>
                    </div>
                </div>

                
                <div id="modeAimPanel" class="hidden space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-900/90 border border-cyan-500/30 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-white text-sm">🎯 Aim & Reflex CyberTest (Velocidad y Precisión)</h3>
                            <p class="text-xs text-slate-400">Haz clic rápidamente en los objetivos neón antes de que termine el tiempo de 10 segundos.</p>
                        </div>
                        <button type="button" onclick="startAimGame()" id="startAimBtn"
                                class="px-4 py-2 rounded-xl font-bold text-xs bg-cyan-500 hover:bg-cyan-400 text-slate-950 transition">
                            ▶ Iniciar Test (10s)
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-xs px-2">
                        <span>Tiempo restante: <strong id="aimTimer" class="text-amber-300 font-gaming">10s</strong></span>
                        <span>Aciertos: <strong id="aimHits" class="text-emerald-400 font-gaming">0</strong></span>
                        <span>Clics fallados: <strong id="aimMisses" class="text-rose-400 font-gaming">0</strong></span>
                    </div>

                    <div id="aimArenaBox" onclick="registerAimMiss(event)"
                         class="relative w-full h-72 rounded-2xl bg-slate-950 border-2 border-slate-800 overflow-hidden cursor-crosshair select-none flex items-center justify-center">
                        <span id="aimPlaceholderText" class="text-xs text-slate-500">Presiona "Iniciar Test (10s)" para comenzar el entrenamiento de reflejos</span>
                        <div id="aimTarget" onclick="hitAimTarget(event)"
                             class="hidden absolute w-12 h-12 rounded-full bg-gradient-to-tr from-cyan-400 to-violet-500 shadow-lg shadow-cyan-400/50 border-2 border-white flex items-center justify-center font-gaming font-bold text-xs text-slate-950 cursor-pointer transition-transform duration-75">
                            HIT
                        </div>
                    </div>
                </div>

                
                <div id="modeTacticalPanel" class="hidden space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-900/90 border border-emerald-500/30">
                        <h3 class="font-bold text-white text-sm">🧠 Simulador Táctico de Capitán (IGL)</h3>
                        <p class="text-xs text-slate-400">Evalúa tu toma de decisiones en momentos críticos de un torneo relámpago.</p>
                    </div>

                    <div id="tacticalContainer" class="p-6 rounded-2xl bg-slate-950/90 border border-slate-800 space-y-4">
                        <div id="tacticalScenarioText" class="font-bold text-sm sm:text-base text-white"></div>
                        <div id="tacticalOptionsList" class="space-y-2.5"></div>
                    </div>
                </div>

                
                <div id="testResultAlert" class="hidden mt-5 p-5 rounded-2xl border">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div id="resultTitle" class="font-gaming font-black text-base"></div>
                            <p id="resultDescription" class="text-xs mt-1"></p>
                        </div>
                        <button type="button" onclick="resetCurrentMode()"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white border border-slate-700 hover:bg-slate-800">
                            🔄 Volver a Intentar
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="cyber-card rounded-3xl p-6 border border-cyan-500/25">
                <h3 class="font-gaming text-base font-bold text-white mb-3 flex items-center gap-2">
                    <span>💡</span> Modalidades Solitarias y de Programación Soportadas en LobbyMatch
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800">
                        <strong class="text-violet-300 block mb-1">1. CodeRoyale / Hackathon Relámpago (1v1 o Squads)</strong>
                        <p class="text-slate-400">Competencias estilo <em>Codingame / LeetCode Battle</em> donde estudiantes de Sistemas USFX resuelven algoritmos en tiempo límite.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800">
                        <strong class="text-cyan-300 block mb-1">2. Cazadores de Bugs (Debugging Speedrun)</strong>
                        <p class="text-slate-400">Modo solitario donde el jugador encuentra el error en un bloque de código PHP/Laravel o SQL contra el reloj para subir su MMR.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800">
                        <strong class="text-emerald-300 block mb-1">3. Arena de Reflejos & Typing Gamer</strong>
                        <p class="text-slate-400">Entrenamiento individual de puntería (Aim Trainer) y velocidad de reacción en milisegundos antes de entrar a un torneo oficial.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900/90 border border-slate-800">
                        <strong class="text-amber-300 block mb-1">4. Retas Solitarias 1v1 (Solo Queue Ladder)</strong>
                        <p class="text-slate-400">Ranking individual donde cada victoria suma puntos de experiencia y muestra si el jugador está en racha ascendente 📈 o en caída 📉.</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="lg:col-span-5 space-y-6">
            
            <div class="cyber-card rounded-3xl p-6 border border-emerald-500/30">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <h2 class="font-gaming text-base font-bold text-white">RADAR DE SQUADS EN TIEMPO REAL</h2>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Actualización automática cada 3s · Última sincr.: <span id="lastSyncTime" class="text-cyan-300 font-mono">ahora</span></p>
                    </div>
                    <button type="button" onclick="fetchLiveSquads(true)"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 transition">
                        ⚡ Simular Acción Ahora
                    </button>
                </div>

                <div id="liveSquadsContainer" class="space-y-3">
                    @foreach($squads as $sq)
                        <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2 py-1 rounded-lg font-gaming font-bold text-xs text-white" style="background-color: {{ $sq->logo_color }}">
                                        {{ $sq->tag }}
                                    </span>
                                    <div>
                                        <span class="font-bold text-sm text-white block leading-tight">{{ $sq->name }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $sq->primary_game }} · {{ $sq->active_members_count }}/{{ $sq->max_members }} miembros</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-gaming font-bold text-xs text-emerald-400">{{ $sq->wins }} W</span>
                                    <span class="block text-[10px] text-emerald-300">📈 Subiendo</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            
            <div class="cyber-card rounded-3xl p-6 border border-violet-500/30">
                <h2 class="font-gaming text-base font-bold text-white mb-3 flex items-center gap-2">
                    <span>📡</span> Transmisión de Actividad en Vivo
                </h2>
                <div id="liveEventsFeed" class="space-y-2.5 max-h-80 overflow-y-auto pr-1 text-xs">
                    <div class="p-3 rounded-xl bg-slate-900 text-slate-400">Conectando con el radar en tiempo real...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = @json(csrf_token());
    const submitUrl = @json(route('arena.submit'));
    const feedUrl = @json(route('arena.feed'));

    let accuracyHistory = [65, 72, 55, 80, 90];

    const codeQuestions = [
        {
            q: "1. En Laravel Eloquent, ¿qué método define una relación Muchos a Muchos entre Squad y User usando la tabla pivote 'squad_members'?",
            code: "public function members() {\n    return $this->__________(User::class, 'squad_members');\n}",
            options: [
                { text: "belongsToMany()", correct: true },
                { text: "hasOneThrough()", correct: false },
                { text: "morphTo()", correct: false },
                { text: "belongsTo()", correct: false }
            ]
        },
        {
            q: "2. ¿Cuál es la complejidad temporal (Big-O) de buscar un código de invitación único 'LM-USFX01' con un índice B-Tree en MySQL?",
            code: "SELECT * FROM squads WHERE invite_code = 'LM-USFX01' LIMIT 1;",
            options: [
                { text: "O(log n) — Búsqueda rápida en árbol indexado", correct: true },
                { text: "O(n!) — Permutación factorial completa", correct: false },
                { text: "O(n²) — Doble bucle anidado", correct: false },
                { text: "No se puede usar WHERE con strings", correct: false }
            ]
        },
        {
            q: "3. En PHP 8.2 / Laravel, ¿qué algoritmo seguro utiliza Hash::make($password) por defecto para cumplir la LobbyMatch?",
            code: "$user->password = Hash::make($request->password);",
            options: [
                { text: "Bcrypt (con factor de costo configurable)", correct: true },
                { text: "Texto plano Base64 sin sal", correct: false },
                { text: "CRC32 reversible", correct: false },
                { text: "ROT13 simple", correct: false }
            ]
        },
        {
            q: "4. ¿Qué imprimirá este fragmento de lógica de emparejamiento (Matchmaking) si hay 8 Squads inscritos?",
            code: "$squads = 8;\n$matchesPrimeraRonda = $squads / 2;\necho $matchesPrimeraRonda;",
            options: [
                { text: "4 enfrentamientos directos en Cuartos de Final", correct: true },
                { text: "16 enfrentamientos", correct: false },
                { text: "2 enfrentamientos", correct: false },
                { text: "Error de sintaxis", correct: false }
            ]
        }
    ];

    let currentCodeIndex = 0;
    let codeCorrectCount = 0;

    function renderCodeQuestion() {
        const item = codeQuestions[currentCodeIndex];
        document.getElementById('codeQuestionCounter').textContent = `Reto ${currentCodeIndex + 1} / ${codeQuestions.length}`;
        document.getElementById('codeQuestionText').textContent = item.q;
        document.getElementById('codeSnippetBox').textContent = item.code;

        const list = document.getElementById('codeOptionsList');
        list.innerHTML = '';

        item.options.forEach((opt) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left px-4 py-3 rounded-xl bg-slate-900 hover:bg-violet-600/25 border border-slate-700 hover:border-violet-500 text-xs sm:text-sm text-slate-200 transition font-medium';
            btn.textContent = opt.text;
            btn.onclick = () => answerCodeQuestion(opt.correct);
            list.appendChild(btn);
        });
    }

    function answerCodeQuestion(isCorrect) {
        if (isCorrect) {
            codeCorrectCount++;
        }
        currentCodeIndex++;
        if (currentCodeIndex < codeQuestions.length) {
            renderCodeQuestion();
        } else {
            const accuracy = Math.round((codeCorrectCount / codeQuestions.length) * 100);
            const score = codeCorrectCount * 50;
            submitTestToBackend('code_royale', score, accuracy);
        }
    }

    let aimTimeLeft = 10;
    let aimHits = 0;
    let aimMisses = 0;
    let aimInterval = null;
    let aimActive = false;

    function startAimGame() {
        aimTimeLeft = 10;
        aimHits = 0;
        aimMisses = 0;
        aimActive = true;
        document.getElementById('aimHits').textContent = '0';
        document.getElementById('aimMisses').textContent = '0';
        document.getElementById('aimTimer').textContent = '10s';
        document.getElementById('aimPlaceholderText').classList.add('hidden');
        document.getElementById('testResultAlert').classList.add('hidden');

        moveAimTarget();

        clearInterval(aimInterval);
        aimInterval = setInterval(() => {
            aimTimeLeft--;
            document.getElementById('aimTimer').textContent = aimTimeLeft + 's';
            if (aimTimeLeft <= 0) {
                clearInterval(aimInterval);
                finishAimGame();
            }
        }, 1000);
    }

    function moveAimTarget() {
        if (!aimActive) return;
        const box = document.getElementById('aimArenaBox');
        const target = document.getElementById('aimTarget');
        target.classList.remove('hidden');

        const maxX = Math.max(20, box.clientWidth - 60);
        const maxY = Math.max(20, box.clientHeight - 60);

        target.style.left = Math.floor(Math.random() * maxX) + 'px';
        target.style.top = Math.floor(Math.random() * maxY) + 'px';
    }

    function hitAimTarget(e) {
        e.stopPropagation();
        if (!aimActive) return;
        aimHits++;
        document.getElementById('aimHits').textContent = aimHits;
        moveAimTarget();
    }

    function registerAimMiss() {
        if (!aimActive) return;
        aimMisses++;
        document.getElementById('aimMisses').textContent = aimMisses;
    }

    function finishAimGame() {
        aimActive = false;
        document.getElementById('aimTarget').classList.add('hidden');
        document.getElementById('aimPlaceholderText').classList.remove('hidden');

        const totalClicks = aimHits + aimMisses;
        const accuracy = totalClicks > 0 ? Math.round((aimHits / totalClicks) * 100) : 0;
        const score = aimHits * 25;
        submitTestToBackend('aim_reflex', score, accuracy);
    }

    const tacticalScenarios = [
        {
            q: "Escenario 1: Quedan 40 segundos en la Gran Final y tu Squad tiene ventaja numérica 4 vs 2. ¿Qué orden das como Capitán?",
            options: [
                { text: "Agrupar al Squad, asegurar posición alta/control de mapa y jugar al tradeo sin regalar duelos 1v1.", correct: true },
                { text: "Separarse los 4 por distintos lados sin comunicación para buscar kills rápidas.", correct: false },
                { text: "Desconectarse del canal de voz.", correct: false }
            ]
        },
        {
            q: "Escenario 2: Tu Squad quiere inscribirse a un torneo de LobbyMatch que exige mínimo 3 integrantes, pero solo son 2 en el roster.",
            options: [
                { text: "Compartir el código único LM-XXXXXX del Squad para reclutar al tercer integrante antes del cierre.", correct: true },
                { text: "Intentar crear cuentas duplicadas con el mismo correo.", correct: false },
                { text: "No revisar los requisitos del torneo.", correct: false }
            ]
        }
    ];
    let currentTacticalIndex = 0;
    let tacticalCorrect = 0;

    function renderTacticalQuestion() {
        const item = tacticalScenarios[currentTacticalIndex];
        document.getElementById('tacticalScenarioText').textContent = item.q;
        const list = document.getElementById('tacticalOptionsList');
        list.innerHTML = '';

        item.options.forEach((opt) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left px-4 py-3 rounded-xl bg-slate-900 hover:bg-emerald-500/20 border border-slate-700 hover:border-emerald-500 text-xs sm:text-sm text-slate-200 transition';
            btn.textContent = opt.text;
            btn.onclick = () => {
                if (opt.correct) tacticalCorrect++;
                currentTacticalIndex++;
                if (currentTacticalIndex < tacticalScenarios.length) {
                    renderTacticalQuestion();
                } else {
                    const acc = Math.round((tacticalCorrect / tacticalScenarios.length) * 100);
                    submitTestToBackend('tactical_igl', tacticalCorrect * 80, acc);
                }
            };
            list.appendChild(btn);
        });
    }

    let activeMode = 'code';
    function switchMode(mode) {
        activeMode = mode;
        document.getElementById('testResultAlert').classList.add('hidden');
        document.getElementById('modeCodePanel').classList.toggle('hidden', mode !== 'code');
        document.getElementById('modeAimPanel').classList.toggle('hidden', mode !== 'aim');
        document.getElementById('modeTacticalPanel').classList.toggle('hidden', mode !== 'tactical');

        document.getElementById('tabBtnCode').className = mode === 'code'
            ? 'px-3.5 py-2 rounded-xl text-xs font-bold bg-violet-600 text-white transition'
            : 'px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition';
        document.getElementById('tabBtnAim').className = mode === 'aim'
            ? 'px-3.5 py-2 rounded-xl text-xs font-bold bg-cyan-500 text-slate-950 transition'
            : 'px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition';
        document.getElementById('tabBtnTactical').className = mode === 'tactical'
            ? 'px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 transition'
            : 'px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 transition';

        resetCurrentMode();
    }

    function resetCurrentMode() {
        document.getElementById('testResultAlert').classList.add('hidden');
        if (activeMode === 'code') {
            currentCodeIndex = 0;
            codeCorrectCount = 0;
            renderCodeQuestion();
        } else if (activeMode === 'tactical') {
            currentTacticalIndex = 0;
            tacticalCorrect = 0;
            renderTacticalQuestion();
        }
    }

    async function submitTestToBackend(mode, score, accuracy) {
        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mode, score, accuracy })
            });
            const data = await response.json();

            document.getElementById('statLevel').textContent = data.level;
            document.getElementById('statRank').textContent = data.rank_tier;
            document.getElementById('statWins').textContent = data.wins;
            document.getElementById('statPlayed').textContent = data.matches_played;
            document.getElementById('statWinRate').textContent = data.win_rate + '%';

            const deltaEl = document.getElementById('statDelta');
            deltaEl.textContent = (data.level_delta >= 0 ? '+' : '') + data.level_delta + ' Nivel (' + accuracy + '% precisión)';
            deltaEl.className = data.is_improving ? 'text-[11px] text-emerald-400 font-bold' : 'text-[11px] text-rose-400 font-bold';

            const badgeBox = document.getElementById('playerTrendBadge');
            const badgeTitle = document.getElementById('playerTrendTitle');
            const badgeSub = document.getElementById('playerTrendSub');

            if (data.is_improving) {
                badgeBox.className = 'p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/50 text-right min-w-[250px]';
                badgeTitle.className = 'font-gaming font-black text-lg text-emerald-300 mt-0.5';
                badgeTitle.textContent = '📈 ¡VAS MEJORANDO!';
                badgeSub.textContent = `Precisión ${accuracy}% · Subiste a Nivel ${data.level}`;
            } else {
                badgeBox.className = 'p-4 rounded-2xl bg-rose-500/20 border border-rose-500/50 text-right min-w-[250px]';
                badgeTitle.className = 'font-gaming font-black text-lg text-rose-300 mt-0.5';
                badgeTitle.textContent = '📉 ESTÁS EMPEORANDO';
                badgeSub.textContent = `Precisión ${accuracy}% · Bajaste a Nivel ${data.level}`;
            }

            accuracyHistory.push(Math.max(15, accuracy));
            if (accuracyHistory.length > 6) accuracyHistory.shift();
            renderHistoryBars();

            const alertBox = document.getElementById('testResultAlert');
            alertBox.classList.remove('hidden');
            if (data.is_improving) {
                alertBox.className = 'mt-5 p-5 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-200';
            } else {
                alertBox.className = 'mt-5 p-5 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-200';
            }
            document.getElementById('resultTitle').textContent = data.trend_text;
            document.getElementById('resultDescription').textContent =
                `Obtuviste ${score} puntos con ${accuracy}% de precisión. Tu resultado ya se transmitió en vivo al Radar de Squads.`;

            fetchLiveSquads(false);
        } catch (err) {
            console.error(err);
        }
    }

    function renderHistoryBars() {
        const container = document.getElementById('historyBars');
        container.innerHTML = '';
        accuracyHistory.forEach((val) => {
            const bar = document.createElement('div');
            const heightPx = Math.max(8, Math.round((val / 100) * 38));
            bar.style.height = heightPx + 'px';
            bar.className = 'flex-1 rounded-t transition-all ' + (val >= 60 ? 'bg-emerald-400' : 'bg-rose-500');
            bar.title = val + '%';
            container.appendChild(bar);
        });
    }

    async function fetchLiveSquads(simulate = false) {
        try {
            const url = simulate ? `${feedUrl}?simulate=1` : feedUrl;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            document.getElementById('lastSyncTime').textContent = data.timestamp;

            const squadsBox = document.getElementById('liveSquadsContainer');
            squadsBox.innerHTML = data.squads.map((sq) => {
                const isDown = sq.live_action.includes('📉');
                const trendBadge = isDown
                    ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40">📉 En caída</span>'
                    : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">📈 Mejorando</span>';

                return `
                    <div class="p-3.5 rounded-2xl bg-slate-900/95 border border-slate-800 space-y-2 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="px-2.5 py-1 rounded-lg font-gaming font-bold text-xs text-white" style="background-color: ${sq.logo_color}">
                                    ${sq.tag}
                                </span>
                                <div>
                                    <span class="font-bold text-sm text-white block leading-tight">${sq.name}</span>
                                    <span class="text-[11px] text-slate-400">${sq.primary_game} · ${sq.members_count}/${sq.max_members} roster</span>
                                </div>
                            </div>
                            <div class="text-right space-y-1">
                                <span class="font-gaming font-bold text-xs text-amber-300 block">${sq.wins} Victorias</span>
                                ${trendBadge}
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-950/90 border border-slate-800/90 text-xs flex items-center justify-between gap-2">
                            <span class="text-slate-300 truncate">⚡ <strong>${sq.actor}:</strong> ${sq.live_action}</span>
                            <span class="text-[10px] text-cyan-400 whitespace-nowrap">${sq.updated_ago}</span>
                        </div>
                    </div>
                `;
            }).join('');

            const eventsBox = document.getElementById('liveEventsFeed');
            eventsBox.innerHTML = data.events.map((ev) => `
                <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 flex items-start justify-between gap-2">
                    <div>
                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold text-white mr-1" style="background-color: ${ev.squad_color}">
                            ${ev.squad_tag}
                        </span>
                        <strong class="text-cyan-300">${ev.gamertag}:</strong>
                        <span class="text-slate-200">${ev.message}</span>
                    </div>
                    <span class="text-[10px] text-slate-500 font-mono">${ev.time}</span>
                </div>
            `).join('');
        } catch (e) {
            console.error(e);
        }
    }

    renderCodeQuestion();
    renderTacticalQuestion();
    renderHistoryBars();
    fetchLiveSquads(false);
    setInterval(() => fetchLiveSquads(false), 3500);
</script>
@endsection
