<?php

namespace App\Http\Controllers;

use App\Models\GamerProfile;
use App\Models\PlayerReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GamerProfileController extends Controller
{
    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user()->load([
            'gamerProfile',
            'reviewsReceived.reviewer.gamerProfile',
        ]);

        $profile = $user->gamerProfile ?? GamerProfile::create([
            'user_id' => $user->id,
            'gamertag' => $user->name,
            'favorite_game' => 'Fortnite',
            'platform' => 'PC',
            'game_id' => 'EPIC-' . strtoupper(substr(md5($user->email), 0, 6)),
            'rank_tier' => 'Oro I',
            'level' => 1,
            'reputation_score' => 5.00,
        ]);

        $otherPlayers = User::with('gamerProfile')
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('profile.edit', compact('user', 'profile', 'otherPlayers'));
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gamertag' => ['required', 'string', 'max:60'],
            'favorite_game' => ['required', 'string', 'max:80'],
            'platform' => ['required', 'in:PC,Consola,Móvil'],
            'game_id' => ['required', 'string', 'max:100'],
            'rank_tier' => ['required', 'string', 'max:60'],
            'discord_handle' => ['nullable', 'string', 'max:80'],
            'stream_url' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'name' => $validated['name'],
        ]);

        GamerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'gamertag' => $validated['gamertag'],
                'favorite_game' => $validated['favorite_game'],
                'platform' => $validated['platform'],
                'game_id' => $validated['game_id'],
                'rank_tier' => $validated['rank_tier'],
                'discord_handle' => $validated['discord_handle'] ?? null,
                'stream_url' => $validated['stream_url'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]
        );

        return redirect()->route('profile.edit')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function storeReview(Request $request): RedirectResponse
    {
        /** @var User $reviewer */
        $reviewer = Auth::user();

        $validated = $request->validate([
            'reviewed_user_id' => ['required', 'exists:users,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'badge' => ['required', 'string', 'max:60'],
            'comment' => ['required', 'string', 'max:350'],
        ]);

        if ((int) $validated['reviewed_user_id'] === $reviewer->id) {
            return back()->withErrors([
                'reviewed_user_id' => 'No puedes enviarte una autovaloración.',
            ]);
        }

        PlayerReview::create([
            'reviewer_id' => $reviewer->id,
            'reviewed_user_id' => $validated['reviewed_user_id'],
            'rating' => $validated['rating'],
            'badge' => $validated['badge'],
            'comment' => $validated['comment'],
        ]);

        $avgRating = PlayerReview::where('reviewed_user_id', $validated['reviewed_user_id'])->avg('rating') ?? 5.00;
        GamerProfile::where('user_id', $validated['reviewed_user_id'])
            ->update(['reputation_score' => round((float) $avgRating, 2)]);

        return back()->with('success', 'Valoración registrada.');
    }
}
