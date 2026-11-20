<?php

namespace App\Http\Controllers;

use App\Models\GamerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Bienvenido a LobbyMatch, ' . Auth::user()->name . '.');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gamertag' => ['nullable', 'string', 'max:60'],
            'favorite_game' => ['nullable', 'string', 'max:80'],
            'platform' => ['nullable', 'in:PC,Consola,Móvil'],
            'game_id' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'player',
            'status' => 'active',
        ]);

        GamerProfile::create([
            'user_id' => $user->id,
            'gamertag' => $validated['gamertag'] ?: $validated['name'],
            'favorite_game' => $validated['favorite_game'] ?: 'Fortnite',
            'platform' => $validated['platform'] ?: 'PC',
            'game_id' => $validated['game_id'] ?: ('EPIC-' . strtoupper(substr(md5($user->email), 0, 6))),
            'rank_tier' => 'Oro I',
            'level' => 1,
            'reputation_score' => 5.00,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Cuenta creada correctamente.');
    }

    public function quickDemoLogin(Request $request, string $role): RedirectResponse
    {
        $emailMap = [
            'admin' => 'admin@lobbymatch.gg',
            'captain' => 'capitan@lobbymatch.gg',
            'player' => 'jugador@lobbymatch.gg',
        ];

        $email = $emailMap[$role] ?? 'jugador@lobbymatch.gg';
        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Usuario no encontrado.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Sesión activa como ' . $user->name . '.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Sesión cerrada.');
    }
}
