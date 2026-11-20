@extends('layouts.app')

@section('title', '[' . $squad->tag . '] ' . $squad->name . ' — Sala de Squad')

@section('content')
<div class="space-y-8">
    
    <div class="cyber-card rounded-3xl p-6 sm:p-8 border border-violet-500/30 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-gaming font-black text-xl text-white shadow-xl"
                 style="background-color: {{ $squad->logo_color }}">
                {{ $squad->tag }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="font-gaming text-2xl sm:text-3xl font-bold text-white">{{ $squad->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-violet-500/20 text-violet-300 border border-violet-500/40">
                        {{ $squad->primary_game }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/20 text-cyan-300">
                        📍 {{ $squad->region }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    {{ $squad->description ?: 'Escuadra oficial registrada en LobbyMatch.' }}
                </p>
                @if($squad->discord_invite)
                    <p class="text-xs text-indigo-300 mt-1">
                        💬 Canal de Coordinación / Contacto: <strong>{{ $squad->discord_invite }}</strong>
                    </p>
                @endif
            </div>
        </div>

        
        <div class="p-4 rounded-2xl bg-slate-950/90 border border-cyan-500/40 flex items-center gap-4">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-slate-400 block">Código de Invitación Oficial</span>
                <code id="squadInviteCode" class="font-gaming font-black text-lg text-cyan-300 tracking-wider">{{ $squad->invite_code }}</code>
            </div>
            <button type="button"
                    onclick="navigator.clipboard.writeText('{{ $squad->invite_code }}'); this.textContent='¡Copiado!';"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-200 border border-cyan-500/40 transition">
                📋 Copiar Código
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-6 space-y-6">
            <div class="cyber-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-gaming text-lg font-bold text-white flex items-center gap-2">
                        <span>👥</span> Roster Oficial ({{ $squad->activeMembers->count() }} / {{ $squad->max_members }})
                    </h2>
                    @if($isMember && (int) $squad->leader_id !== $user->id)
                        <form action="{{ route('squads.leave', $squad) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-lg text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                Salir del Squad
                            </button>
                        </form>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach($squad->activeMembers as $member)
                        <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-cyan-500 flex items-center justify-center font-bold text-xs text-white">
                                    {{ strtoupper(substr($member->gamerProfile->gamertag ?? $member->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white text-sm">
                                            {{ $member->gamerProfile->gamertag ?? $member->name }}
                                        </span>
                                        @if($member->pivot->role === 'captain' || (int) $squad->leader_id === $member->id)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                                👑 Capitán
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-800 text-slate-300">
                                                Miembro
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        ID Juego: <code class="text-cyan-300">{{ $member->gamerProfile->game_id ?? 'N/A' }}</code>
                                        · {{ $member->gamerProfile->platform ?? 'PC' }}
                                        · {{ $member->gamerProfile->rank_tier ?? 'Oro I' }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-right text-xs">
                                <span class="text-amber-300 font-bold">★ {{ number_format((float) ($member->gamerProfile->reputation_score ?? 5.0), 1) }}</span>
                                @if($member->gamerProfile?->discord_handle)
                                    <span class="block text-[11px] text-indigo-300">{{ $member->gamerProfile->discord_handle }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            
            @if($isCaptain && $squad->pendingMembers->isNotEmpty())
                <div class="cyber-card rounded-2xl p-6 border border-amber-500/40">
                    <h3 class="font-gaming text-base font-bold text-amber-300 mb-3">
                        🔔 Solicitudes de Ingreso Pendientes ({{ $squad->pendingMembers->count() }})
                    </h3>
                    <div class="space-y-2.5">
                        @foreach($squad->pendingMembers as $aspirant)
                            <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-white text-sm">{{ $aspirant->gamerProfile->gamertag ?? $aspirant->name }}</span>
                                    <span class="block text-xs text-slate-400">
                                        ID: {{ $aspirant->gamerProfile->game_id ?? 'Sin ID' }} · {{ $aspirant->gamerProfile->platform ?? 'PC' }}
                                    </span>
                                </div>
                                <form action="{{ route('squads.approve', [$squad, $aspirant]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                                        ✓ Aceptar al Roster
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        
        <div class="lg:col-span-6 space-y-6">
            <div class="cyber-card rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-gaming text-lg font-bold text-white flex items-center gap-2">
                            <span>💬</span> Chat Táctico y Avisos del Squad
                        </h2>
                        <span class="text-xs text-emerald-400 font-semibold">● Canal Activo</span>
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1 mb-5">
                        @forelse($squad->messages as $msg)
                            <div class="p-3.5 rounded-xl {{ $msg->is_pinned ? 'bg-violet-500/15 border border-violet-500/40' : 'bg-slate-900/90 border border-slate-800' }}">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-cyan-300">
                                            {{ $msg->user->gamerProfile->gamertag ?? $msg->user->name }}
                                        </span>
                                        @if($msg->is_pinned)
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-violet-500/30 text-violet-200">📌 Aviso Fijado</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-500">{{ $msg->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-200">{{ $msg->message }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">Aún no hay mensajes en la sala del Squad.</p>
                        @endforelse
                    </div>
                </div>

                @if($isMember || $user->isAdmin())
                    <form action="{{ route('squads.messages.store', $squad) }}" method="POST" class="space-y-3 pt-3 border-t border-slate-800">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="message" required
                                   placeholder="Escribe estrategia, horario de entrenamiento o código de sala..."
                                   class="flex-1 px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs sm:text-sm focus:outline-none focus:border-violet-500">
                            <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-violet-600 to-cyan-500 text-white">
                                Enviar
                            </button>
                        </div>
                        @if($isCaptain)
                            <label class="inline-flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                                <input type="checkbox" name="is_pinned" value="1" class="rounded bg-slate-900 border-slate-700 text-violet-600">
                                <span>Fijar como aviso oficial del Capitán</span>
                            </label>
                        @endif
                    </form>
                @else
                    <div class="p-3 rounded-xl bg-slate-900 text-xs text-slate-400 text-center">
                        Únete a este Squad con el código <code class="text-cyan-300 font-bold">{{ $squad->invite_code }}</code> para participar en el chat interno.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
