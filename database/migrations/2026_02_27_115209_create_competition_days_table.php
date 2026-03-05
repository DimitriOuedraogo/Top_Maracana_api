<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('competition_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0=Dimanche, 6=Samedi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_days');
    }
};