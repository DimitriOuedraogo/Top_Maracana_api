<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('group_id')->nullable()->constrained('groups')->onDelete('cascade');
            $table->foreignUuid('home_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignUuid('away_team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('week_number');   // Semaine 1, 2, 3...
            $table->tinyInteger('day_of_week'); // 0=Lundi, 6=Dimanche
            $table->time('match_time');        // 10:00, 15:00...
            $table->enum('round_type', ['group', 'quarter', 'semi', 'final'])->default('group');
            $table->enum('status', ['scheduled', 'played', 'postponed'])->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};