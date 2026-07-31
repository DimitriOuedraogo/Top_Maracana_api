<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE competitions MODIFY COLUMN status ENUM('draft','registration_open','full','ongoing','finished') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE competitions MODIFY COLUMN status ENUM('registration_open','full','ongoing','finished') NOT NULL DEFAULT 'registration_open'");
    }
};
