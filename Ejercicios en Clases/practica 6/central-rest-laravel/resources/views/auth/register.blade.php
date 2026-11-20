@extends('layouts.app')

@section('title', 'Registro de Nuevo Gamer — LobbyMatch')

@section('content')
<div class="max-w-2xl mx-auto my-6">
    <div class="cyber-card rounded-3xl p-8 shadow-2xl border border-violet-500/30">
        <div class="text-center mb-6">
            <h1 class="font-gaming text-2xl font-bold text-white">CREAR CUENTA DE GAMER</h1>
            <p class="text-xs text-slate-400 mt-1">Regístrate de forma segura con cifrado Bcrypt y configura tu identidad competitiva</p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Nombre Completo *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Ej. Carlos Mendoza"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Correo Electrónico Único *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="carlos@usfx.bo"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Contraseña Segura (Mín. 8) *</label>
                    <input type="password" name="password" required
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Confirmar Contraseña *</label>
                    <input type="password" name="password_confirmation" required
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800">
                <p class="text-xs font-semibold text-cyan-400 uppercase tracking-wider mb-3">Datos Iniciales de Perfil Gamer (Opcional, editable luego)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Apodo / Gamertag</label>
                        <input type="text" name="gamertag" value="{{ old('gamertag') }}"
                               placeholder="Ej. ShadowSniperBO"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Juego Favorito</label>
                        <select name="favorite_game" class="w-full px-3.5 py-2 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm">
                            <option value="Fortnite">Fortnite</option>
                            <option value="Valorant">Valorant</option>
                            <option value="Dota 2">Dota 2</option>
                            <option value="Counter-Strike 2">Counter-Strike 2</option>
                            <option value="Mobile Legends">Mobile Legends</option>
                            <option value="League of Legends">League of Legends</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Plataforma Principal</label>
                        <select name="platform" class="w-full px-3.5 py-2 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm">
                            <option value="PC">PC</option>
                            <option value="Consola">Consola</option>
                            <option value="Móvil">Móvil</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">ID del Juego (Epic / Riot / Steam)</label>
                        <input type="text" name="game_id" value="{{ old('game_id') }}"
                               placeholder="Ej. ShadowBO#LAS"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm">
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-violet-600 to-cyan-500 hover:from-violet-500 hover:to-cyan-400 text-white shadow-lg shadow-violet-600/30 transition">
                Completar Registro e Ir al Dashboard
            </button>

            <p class="text-center text-xs text-slate-400">
                ¿Ya tienes una cuenta registrada? <a href="{{ route('login') }}" class="text-cyan-400 hover:underline font-semibold">Inicia sesión aquí</a>
            </p>
        </form>
    </div>
</div>
@endsection
