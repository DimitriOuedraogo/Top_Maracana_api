<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->foreignUuid('top_scorer_id')->nullable()->after('winner_id')
                ->constrained('players')->onDelete('set null');
            $table->foreignUuid('best_player_id')->nullable()->after('top_scorer_id')
                ->constrained('players')->onDelete('set null');
            $table->foreignUuid('best_goalkeeper_id')->nullable()->after('best_player_id')
                ->constrained('players')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropForeign(['top_scorer_id']);
            $table->dropForeign(['best_player_id']);
            $table->dropForeign(['best_goalkeeper_id']);
            $table->dropColumn(['top_scorer_id', 'best_player_id', 'best_goalkeeper_id']);
        });
    }
};
