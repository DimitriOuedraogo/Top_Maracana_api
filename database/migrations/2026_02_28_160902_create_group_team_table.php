<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_team', function (Blueprint $table) {

            $table->foreignUuid('group_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignUuid('team_id')
                ->constrained()
                ->onDelete('cascade');

            // Clé primaire composite
            $table->primary(['group_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_team');
    }
};