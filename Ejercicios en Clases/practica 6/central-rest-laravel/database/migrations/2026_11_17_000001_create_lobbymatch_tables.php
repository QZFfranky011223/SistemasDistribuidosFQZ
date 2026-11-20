<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('gamertag');
            $table->string('favorite_game')->default('Fortnite');
            $table->string('platform')->default('PC');
            $table->string('game_id');
            $table->string('rank_tier')->default('Oro I');
            $table->integer('level')->default(1);
            $table->string('discord_handle')->nullable();
            $table->string('stream_url')->nullable();
            $table->text('bio')->nullable();
            $table->integer('wins')->default(0);
            $table->integer('matches_played')->default(0);
            $table->decimal('reputation_score', 3, 2)->default(5.00);
            $table->timestamps();
        });

        Schema::create('squads', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('tag', 8)->unique();
            $table->string('logo_color')->default('#8b5cf6');
            $table->string('primary_game')->default('Fortnite');
            $table->string('region')->default('Sucre / USFX');
            $table->text('description')->nullable();
            $table->string('discord_invite')->nullable();
            $table->foreignId('leader_id')->constrained('users')->cascadeOnDelete();
            $table->string('invite_code', 16)->unique();
            $table->integer('max_members')->default(5);
            $table->boolean('is_recruiting')->default(true);
            $table->integer('wins')->default(0);
            $table->timestamps();
        });

        Schema::create('squad_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('squad_id')->constrained('squads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->string('status')->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['squad_id', 'user_id']);
        });

        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('game');
            $table->string('zone_or_community')->default('USFX / Sucre');
            $table->string('format')->default('Eliminación Directa');
            $table->integer('min_squad_members')->default(2);
            $table->integer('max_teams')->default(8);
            $table->string('prize_pool')->default('Bs. 1,000 + Trofeo');
            $table->dateTime('start_date');
            $table->string('status')->default('open');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('tournament_squad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('squad_id')->constrained('squads')->cascadeOnDelete();
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->integer('seed_number')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'squad_id']);
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->string('round_name')->default('Cuartos de Final');
            $table->integer('match_number')->default(1);
            $table->foreignId('squad_a_id')->nullable()->constrained('squads')->nullOnDelete();
            $table->foreignId('squad_b_id')->nullable()->constrained('squads')->nullOnDelete();
            $table->integer('score_a')->default(0);
            $table->integer('score_b')->default(0);
            $table->foreignId('winner_squad_id')->nullable()->constrained('squads')->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('lobby_code')->nullable();
            $table->timestamps();
        });

        Schema::create('squad_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('squad_id')->constrained('squads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('player_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('badge')->default('GG / Fair Play');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_reviews');
        Schema::dropIfExists('squad_messages');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('tournament_squad');
        Schema::dropIfExists('tournaments');
        Schema::dropIfExists('squad_members');
        Schema::dropIfExists('squads');
        Schema::dropIfExists('gamer_profiles');
    }
};
