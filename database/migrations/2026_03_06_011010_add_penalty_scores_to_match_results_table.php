<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('match_results', function (Blueprint $table) {
            $table->integer('home_penalty_score')->nullable()->after('away_score');
            $table->integer('away_penalty_score')->nullable()->after('home_penalty_score');
        });
    }

    public function down(): void
    {
        Schema::table('match_results', function (Blueprint $table) {
            $table->dropColumn(['home_penalty_score', 'away_penalty_score']);
        });
    }
};