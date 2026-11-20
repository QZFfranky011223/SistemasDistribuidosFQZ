<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LobbyMatch — Torneos y Squads')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        obsidian: '#070a12',
                        panel: '#0f1626',
                        panelLight: '#162038',
                        neonPurple: '#8b5cf6',
                        neonCyan: '#06b6d4',
                        neonEmerald: '#10b981',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #070a12;
            background-image:
                radial-gradient(circle at 15% 10%, rgba(139, 92, 246, 0.14), transparent 30%),
                radial-gradient(circle at 85% 15%, rgba(6, 182, 212, 0.12), transparent 30%),
                radial-gradient(circle at 50% 90%, rgba(16, 185, 129, 0.08), transparent 35%);
            color: #e2e8f0;
        }
        .font-gaming {
            font-family: 'Orbitron', sans-serif;
        }
        .cyber-card {
            background: rgba(15, 22, 38, 0.88);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(148, 163, 184, 0.14);
        }
        .cyber-card:hover {
            border-color: rgba(139, 92, 246, 0.45);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-violet-500 selection:text-white">

    <header class="sticky top-0 z-40 bg-[#090d18]/95 backdrop-blur-md border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-600 via-indigo-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-violet-600/30 group-hover:scale-105 transition">
                        <span class="font-gaming font-black text-white text-lg">LM</span>
                    </div>
                    <div>
                        <span class="font-gaming font-bold text-lg tracking-wider bg-gradient-to-r from-white via-violet-200 to-cyan-300 bg-clip-text text-transparent">
                            LOBBYMATCH
                        </span>
                        <span class="block text-[10px] uppercase tracking-widest text-cyan-400 font-semibold -mt-1">
                            Torneos & Squads
                        </span>
                    </div>
                </a>

                <nav class="hidden md:flex items-center gap-1">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('dashboard') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-bold transition {{ request()->routeIs('dashboard') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-amber-300 hover:text-white hover:bg-slate-800/60' }}">
                                📡 Monitor & Transmisión
                            </a>
                            <a href="{{ route('tournaments.index') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('tournaments.*') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                                🏆 Administrar Torneos
                            </a>
                            <a href="{{ route('squads.index') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('squads.*') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                                🛡️ Ver Squads
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-bold transition {{ request()->routeIs('dashboard') ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                                🎮 Jugar & Dashboard
                            </a>
                            <a href="{{ route('squads.index') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('squads.*') ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                                🛡️ Mi Squad
                            </a>
                            <a href="{{ route('tournaments.index') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('tournaments.*') ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                                🏆 Torneos
                            </a>
                            <a href="{{ route('arena.index') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('arena.*') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'text-emerald-400 hover:text-white hover:bg-emerald-500/15' }}">
                                ⚡ Tests & Ranking
                            </a>
                            <a href="{{ route('profile.edit') }}"
                               class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('profile.*') ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                                ⚙️ Mi Perfil
                            </a>
                        @endif
                    @else
                        <a href="{{ route('home') }}"
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('home') ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Inicio
                        </a>
                        <a href="{{ route('tournaments.index') }}"
                           class="px-3.5 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('tournaments.*') ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            🏆 Torneos
                        </a>
                    @endauth
                </nav>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <div class="hidden sm:flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-900/90 border border-slate-800">
                        <div class="w-8 h-8 rounded-lg {{ auth()->user()->isAdmin() ? 'bg-gradient-to-tr from-amber-500 to-orange-600 text-slate-950' : 'bg-gradient-to-tr from-violet-600 to-cyan-500 text-white' }} flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-semibold text-white leading-tight">
                                {{ auth()->user()->isAdmin() ? auth()->user()->name : (auth()->user()->gamerProfile->gamertag ?? auth()->user()->name) }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                @if(auth()->user()->isAdmin())
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/40">Administrador</span>
                                @elseif(auth()->user()->isCaptain())
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-violet-500/20 text-violet-300 border border-violet-500/40">Jugador · Capitán</span>
                                @else
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">Jugador</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-lg text-xs font-semibold bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 transition">
                            Cerrar Sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-200 hover:text-white hover:bg-slate-800 transition">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-r from-violet-600 to-cyan-500 hover:from-violet-500 hover:to-cyan-400 text-white shadow-lg shadow-violet-600/25 transition">
                        Registrarse
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-200 flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-200 shadow-lg">
                <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                    <span>⚠️</span>
                    <span>Verifica los siguientes datos:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-rose-300">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <footer class="border-t border-slate-800/80 bg-[#060911] mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <div class="flex items-center gap-3">
                <span class="font-gaming font-bold text-slate-200">LOBBYMATCH</span>
                <span>· Plataforma de Torneos y Gestión de Squads</span>
            </div>
            <div>
                USFX · Sucre
            </div>
        </div>
    </footer>

</body>
</html>
