<?php

namespace Database\Seeders;

use App\Models\GameMatch;
use App\Models\GamerProfile;
use App\Models\PlayerReview;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\SquadMessage;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $admin = User::create([
            'name' => 'Admin General LobbyMatch',
            'email' => 'admin@lobbymatch.gg',
            'password' => $password,
            'role' => 'admin',
            'status' => 'active',
        ]);

        GamerProfile::create([
            'user_id' => $admin->id,
            'gamertag' => 'MasterCasterBO',
            'favorite_game' => 'Valorant',
            'platform' => 'PC',
            'game_id' => 'RIOT-ADMIN#BO1',
            'rank_tier' => 'Unreal / Radiante',
            'level' => 99,
            'discord_handle' => 'mastercaster_bo#0001',
            'stream_url' => 'https://twitch.tv/lobbymatch_bo',
            'bio' => 'Organizador de Torneos USFX y Liga Local Sucre.',
            'wins' => 42,
            'matches_played' => 50,
            'reputation_score' => 5.00,
        ]);

        $captain1 = User::create([
            'name' => 'Alejandro Vargas',
            'email' => 'capitan@lobbymatch.gg',
            'password' => $password,
            'role' => 'captain',
            'status' => 'active',
        ]);

        GamerProfile::create([
            'user_id' => $captain1->id,
            'gamertag' => 'VortexUSFX',
            'favorite_game' => 'Fortnite',
            'platform' => 'PC',
            'game_id' => 'EPIC-VortexBO99',
            'rank_tier' => 'Inmortal',
            'level' => 54,
            'discord_handle' => 'vortex_usfx#4410',
            'stream_url' => 'https://kick.com/vortexusfx',
            'bio' => 'IGL y Capitán de USFX Titans.',
            'wins' => 28,
            'matches_played' => 35,
            'reputation_score' => 4.95,
        ]);

        $freePlayer = User::create([
            'name' => 'Diego Quispe',
            'email' => 'jugador@lobbymatch.gg',
            'password' => $password,
            'role' => 'player',
            'status' => 'active',
        ]);

        GamerProfile::create([
            'user_id' => $freePlayer->id,
            'gamertag' => 'ShadowSniperSucre',
            'favorite_game' => 'Fortnite',
            'platform' => 'Consola',
            'game_id' => 'EPIC-ShadowSucre77',
            'rank_tier' => 'Diamante II',
            'level' => 32,
            'discord_handle' => 'shadow_sucre#7788',
            'bio' => 'Disponible para unirse a un Squad.',
            'wins' => 15,
            'matches_played' => 22,
            'reputation_score' => 4.85,
        ]);

        $member1 = User::create([
            'name' => 'Camila Rojas',
            'email' => 'camila@lobbymatch.gg',
            'password' => $password,
            'role' => 'player',
            'status' => 'active',
        ]);
        GamerProfile::create([
            'user_id' => $member1->id,
            'gamertag' => 'ValkyriaBO',
            'favorite_game' => 'Fortnite',
            'platform' => 'PC',
            'game_id' => 'EPIC-ValkyriaBO',
            'rank_tier' => 'Ascendente',
            'level' => 41,
            'discord_handle' => 'valkyria#2026',
            'bio' => 'Support en escuadras competitivas.',
            'wins' => 21,
            'matches_played' => 29,
            'reputation_score' => 5.00,
        ]);

        $member2 = User::create([
            'name' => 'Mateo Flores',
            'email' => 'mateo@lobbymatch.gg',
            'password' => $password,
            'role' => 'player',
            'status' => 'active',
        ]);
        GamerProfile::create([
            'user_id' => $member2->id,
            'gamertag' => 'KronoZ',
            'favorite_game' => 'Valorant',
            'platform' => 'PC',
            'game_id' => 'KronoZ#LAS1',
            'rank_tier' => 'Diamante II',
            'level' => 38,
            'discord_handle' => 'kronoz_bo#9012',
            'bio' => 'Duelista / Entry fragger.',
            'wins' => 19,
            'matches_played' => 25,
            'reputation_score' => 4.80,
        ]);

        $captain2 = User::create([
            'name' => 'Rodrigo Arancibia',
            'email' => 'rodrigo@lobbymatch.gg',
            'password' => $password,
            'role' => 'captain',
            'status' => 'active',
        ]);
        GamerProfile::create([
            'user_id' => $captain2->id,
            'gamertag' => 'DrakoSucre',
            'favorite_game' => 'Valorant',
            'platform' => 'PC',
            'game_id' => 'Drako#SUCRE',
            'rank_tier' => 'Inmortal',
            'level' => 60,
            'discord_handle' => 'drako_sucre#1111',
            'bio' => 'Capitán de Sucre Vipers.',
            'wins' => 33,
            'matches_played' => 40,
            'reputation_score' => 4.90,
        ]);

        $member3 = User::create([
            'name' => 'Lucía Padilla',
            'email' => 'lucia@lobbymatch.gg',
            'password' => $password,
            'role' => 'player',
            'status' => 'active',
        ]);
        GamerProfile::create([
            'user_id' => $member3->id,
            'gamertag' => 'NovaStrike',
            'favorite_game' => 'Valorant',
            'platform' => 'PC',
            'game_id' => 'NovaStrike#BO',
            'rank_tier' => 'Platino',
            'level' => 29,
            'discord_handle' => 'nova_bo#3344',
            'bio' => 'Sentinel & Controller.',
            'wins' => 14,
            'matches_played' => 20,
            'reputation_score' => 4.95,
        ]);

        $captain3 = User::create([
            'name' => 'Fabricio Céspedes',
            'email' => 'fabricio@lobbymatch.gg',
            'password' => $password,
            'role' => 'captain',
            'status' => 'active',
        ]);
        GamerProfile::create([
            'user_id' => $captain3->id,
            'gamertag' => 'AndeanKing',
            'favorite_game' => 'Dota 2',
            'platform' => 'PC',
            'game_id' => 'STEAM-9948102',
            'rank_tier' => 'Inmortal',
            'level' => 70,
            'discord_handle' => 'andeanking#5500',
            'bio' => 'Hard Carry en torneos universitarios.',
            'wins' => 37,
            'matches_played' => 45,
            'reputation_score' => 4.88,
        ]);

        $member4 = User::create([
            'name' => 'Sergio Zárate',
            'email' => 'sergio@lobbymatch.gg',
            'password' => $password,
            'role' => 'player',
            'status' => 'active',
        ]);
        GamerProfile::create([
            'user_id' => $member4->id,
            'gamertag' => 'IllapaMid',
            'favorite_game' => 'Dota 2',
            'platform' => 'PC',
            'game_id' => 'STEAM-8837191',
            'rank_tier' => 'Ascendente',
            'level' => 48,
            'discord_handle' => 'illapa#7711',
            'bio' => 'Midlaner.',
            'wins' => 24,
            'matches_played' => 31,
            'reputation_score' => 4.75,
        ]);

        $squad1 = Squad::create([
            'name' => 'USFX Titans eSports',
            'tag' => 'USFX',
            'logo_color' => '#8b5cf6',
            'primary_game' => 'Fortnite',
            'region' => 'USFX Ingeniería · Sucre',
            'description' => 'Escuadra universitaria de Ingeniería de Sistemas USFX.',
            'discord_invite' => 'discord.gg/usfxtitans',
            'leader_id' => $captain1->id,
            'invite_code' => 'LM-USFX01',
            'max_members' => 5,
            'is_recruiting' => true,
            'wins' => 12,
        ]);

        foreach ([
            [$captain1->id, 'captain'],
            [$member1->id, 'member'],
            [$member2->id, 'member'],
        ] as [$uid, $role]) {
            SquadMember::create([
                'squad_id' => $squad1->id,
                'user_id' => $uid,
                'role' => $role,
                'status' => 'active',
                'joined_at' => now()->subDays(10),
            ]);
        }

        $squad2 = Squad::create([
            'name' => 'Sucre Vipers Gaming',
            'tag' => 'SVPR',
            'logo_color' => '#06b6d4',
            'primary_game' => 'Valorant',
            'region' => 'Sucre Centro',
            'description' => 'Roster competitivo de Valorant y Fortnite.',
            'discord_invite' => 'discord.gg/sucrevipers',
            'leader_id' => $captain2->id,
            'invite_code' => 'LM-SUCR99',
            'max_members' => 5,
            'is_recruiting' => true,
            'wins' => 9,
        ]);

        foreach ([
            [$captain2->id, 'captain'],
            [$member3->id, 'member'],
        ] as [$uid, $role]) {
            SquadMember::create([
                'squad_id' => $squad2->id,
                'user_id' => $uid,
                'role' => $role,
                'status' => 'active',
                'joined_at' => now()->subDays(8),
            ]);
        }

        $squad3 = Squad::create([
            'name' => 'Andean Immortals',
            'tag' => 'ANDN',
            'logo_color' => '#10b981',
            'primary_game' => 'Dota 2',
            'region' => 'Chuquisaca / LATAM Sur',
            'description' => 'Equipo especializado en torneos relámpago de fin de semana.',
            'discord_invite' => 'discord.gg/andeanimmortals',
            'leader_id' => $captain3->id,
            'invite_code' => 'LM-ANDN77',
            'max_members' => 5,
            'is_recruiting' => true,
            'wins' => 7,
        ]);

        foreach ([
            [$captain3->id, 'captain'],
            [$member4->id, 'member'],
        ] as [$uid, $role]) {
            SquadMember::create([
                'squad_id' => $squad3->id,
                'user_id' => $uid,
                'role' => $role,
                'status' => 'active',
                'joined_at' => now()->subDays(6),
            ]);
        }

        SquadMessage::create([
            'squad_id' => $squad1->id,
            'user_id' => $captain1->id,
            'message' => 'Práctica de rotaciones hoy a las 21:00.',
            'is_pinned' => true,
        ]);
        SquadMessage::create([
            'squad_id' => $squad1->id,
            'user_id' => $member1->id,
            'message' => 'Listo, ya actualicé mi ID en el perfil.',
            'is_pinned' => false,
        ]);

        $t1 = Tournament::create([
            'name' => 'Torneo Relámpago USFX Fortnite Squads',
            'game' => 'Fortnite',
            'zone_or_community' => 'USFX Ingeniería / Sucre',
            'format' => 'Battle Royale Custom Lobby',
            'min_squad_members' => 2,
            'max_teams' => 8,
            'prize_pool' => 'Bs. 1,500 + Trofeo USFX',
            'start_date' => now()->addDays(2)->setTime(19, 0),
            'status' => 'open',
            'description' => 'Torneo abierto para Squads universitarios y locales.',
            'created_by' => $admin->id,
        ]);

        $t2 = Tournament::create([
            'name' => 'Sucre CyberCup Valorant 2026',
            'game' => 'Valorant',
            'zone_or_community' => 'Sucre Local eSports',
            'format' => 'Eliminación Directa BO3',
            'min_squad_members' => 2,
            'max_teams' => 8,
            'prize_pool' => 'Bs. 2,000 + Periféricos',
            'start_date' => now()->addHours(3),
            'status' => 'in_progress',
            'description' => 'Competencia con llaves en vivo.',
            'created_by' => $admin->id,
        ]);

        $t3 = Tournament::create([
            'name' => 'Copa Interfacultativa Dota 2 Chuquisaca',
            'game' => 'Dota 2',
            'zone_or_community' => 'USFX / Chuquisaca',
            'format' => 'Captains Mode BO3',
            'min_squad_members' => 2,
            'max_teams' => 4,
            'prize_pool' => 'Bs. 1,200',
            'start_date' => now()->addDays(5)->setTime(18, 30),
            'status' => 'open',
            'description' => 'Torneo universitario de Dota 2.',
            'created_by' => $admin->id,
        ]);

        $t4 = Tournament::create([
            'name' => 'Retas Relámpago Mobile Legends Sucre',
            'game' => 'Mobile Legends',
            'zone_or_community' => 'Comunidad Móvil Sucre',
            'format' => 'Eliminación Simple',
            'min_squad_members' => 2,
            'max_teams' => 4,
            'prize_pool' => 'Bs. 800 + Diamantes',
            'start_date' => now()->subDays(4),
            'status' => 'finished',
            'description' => 'Edición finalizada.',
            'created_by' => $admin->id,
        ]);

        $t1->squads()->attach($squad2->id, [
            'registered_by' => $captain2->id,
            'seed_number' => 1,
            'registered_at' => now()->subHours(5),
        ]);

        $t2->squads()->attach($squad1->id, [
            'registered_by' => $captain1->id,
            'seed_number' => 1,
            'registered_at' => now()->subDay(),
        ]);
        $t2->squads()->attach($squad2->id, [
            'registered_by' => $captain2->id,
            'seed_number' => 2,
            'registered_at' => now()->subDay(),
        ]);
        $t2->squads()->attach($squad3->id, [
            'registered_by' => $captain3->id,
            'seed_number' => 3,
            'registered_at' => now()->subDay(),
        ]);

        GameMatch::create([
            'tournament_id' => $t2->id,
            'round_name' => 'Semifinal #1',
            'match_number' => 1,
            'squad_a_id' => $squad1->id,
            'squad_b_id' => $squad2->id,
            'score_a' => 2,
            'score_b' => 1,
            'winner_squad_id' => $squad1->id,
            'scheduled_at' => now()->subHour(),
            'status' => 'finished',
            'lobby_code' => 'LOBBY-VL91',
        ]);

        GameMatch::create([
            'tournament_id' => $t2->id,
            'round_name' => 'Gran Final',
            'match_number' => 2,
            'squad_a_id' => $squad1->id,
            'squad_b_id' => $squad3->id,
            'score_a' => 1,
            'score_b' => 1,
            'winner_squad_id' => null,
            'scheduled_at' => now()->addHour(),
            'status' => 'live',
            'lobby_code' => 'LOBBY-FIN1',
        ]);

        PlayerReview::create([
            'reviewer_id' => $member1->id,
            'reviewed_user_id' => $captain1->id,
            'rating' => 5,
            'badge' => 'Líder Estratégico (IGL)',
            'comment' => 'Buen capitán en las rotaciones finales.',
        ]);

        PlayerReview::create([
            'reviewer_id' => $captain2->id,
            'reviewed_user_id' => $captain1->id,
            'rating' => 5,
            'badge' => 'GG / Buen Compañero',
            'comment' => 'Partida limpia en la Semifinal.',
        ]);
    }
}
