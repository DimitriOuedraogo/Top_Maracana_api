<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // ← UUID
            $table->foreignUuid('organizer_id')->constrained('users')->onDelete('cascade'); // ← foreignUuid
            $table->string('name');
            $table->string('location');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('max_teams');
            $table->integer('players_per_team');
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->text('prize_description')->nullable();
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->string('poster_image')->nullable();
            $table->enum('status', ['registration_open', 'full', 'ongoing', 'finished'])->default('registration_open');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};