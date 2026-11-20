@extends('layouts.app')

@section('title', 'Iniciar Sesión — LobbyMatch')

@section('content')
<div class="max-w-md mx-auto my-8">
    <div class="cyber-card rounded-3xl p-8 shadow-2xl border border-violet-500/30">
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-600 to-cyan-500 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-violet-600/30">
                <span class="font-gaming font-black text-white text-xl">LM</span>
            </div>
            <h1 class="font-gaming text-2xl font-bold text-white">INICIAR SESIÓN</h1>
            <p class="text-xs text-slate-400 mt-1">Ingresa tus credenciales para acceder a tu cuenta</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Correo Electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="correo@lobbymatch.gg"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Contraseña</label>
                <input type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-sm focus:outline-none focus:border-violet-500">
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-violet-600">
                    <span>Mantener sesión activa</span>
                </label>
                <a href="{{ route('register') }}" class="text-cyan-400 hover:underline font-medium">¿Nuevo jugador? Regístrate</a>
            </div>

            <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm bg-gradient-to-r from-violet-600 to-cyan-500 hover:from-violet-500 hover:to-cyan-400 text-white shadow-lg shadow-violet-600/30 transition">
                Iniciar Sesión
            </button>
        </form>
    </div>
</div>
@endsection
