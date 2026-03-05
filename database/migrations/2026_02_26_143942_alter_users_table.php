<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin','organizer', 'team_manager']);
            // Email verification OTP
            $table->string('email_verification_code', 6)->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_code');

            // Password reset OTP
            $table->string('password_reset_code', 6)->nullable()->after('password');
            $table->timestamp('password_reset_expires_at')->nullable()->after('password_reset_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'email_verification_code',
                'email_verification_expires_at',
                'password_reset_code',
                'password_reset_expires_at',
            ]);
        });
    }
};