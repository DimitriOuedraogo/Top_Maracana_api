<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registration_players', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('registration_id')
                  ->constrained('registrations')
                  ->cascadeOnDelete();
            $table->foreignUuid('player_id')
                  ->constrained('players')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['registration_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_players');
    }
};
