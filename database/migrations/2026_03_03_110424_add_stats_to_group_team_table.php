<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('group_team', function (Blueprint $table) {
            $table->integer('played')->default(0);
            $table->integer('win')->default(0);
            $table->integer('draws')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('goals_for')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->integer('points')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('group_team', function (Blueprint $table) {
            $table->dropColumn([
                'played', 'win', 'draws', 'losses',
                'goals_for', 'goals_against', 'goal_difference', 'points'
            ]);
        });
    }
};