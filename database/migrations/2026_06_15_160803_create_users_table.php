<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // ── Identity ──────────────────────────────────────────
            $table->string('name');

            // Phone is the primary login field (mandatory)
            $table->string('phone', 20)->unique();

            // Email optional — used for OTP delivery when provided
            $table->string('email')->unique()->nullable();

            $table->string('password');

            // ── Role ─────────────────────────────────────────────
            // rider  : can find and request rides
            // driver : can post rides, accept/decline requests
            // admin  : full platform management
            $table->enum('role', ['rider', 'driver', 'admin'])
                  ->default('rider');

            // ── RCCG membership ───────────────────────────────────
            // Self-reported; admin manually verifies against records
            $table->string('rccg_member_id', 60)->nullable();

            // ── OTP verification ──────────────────────────────────
            // Hashed 6-digit code stored on the user row.
            // Null once verified or expired.
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            // ── Account state ─────────────────────────────────────
            // is_verified : admin (or successful OTP) sets to true
            // is_active   : admin can deactivate without deleting
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);

            // Profile photo — stored locally under storage/app/public
            $table->string('profile_photo')->nullable();

            // ── Timestamps ────────────────────────────────────────
            $table->timestamps();
            $table->softDeletes(); // safe delete — keeps FK integrity

            // ── Indexes ───────────────────────────────────────────
            $table->index('role');
            $table->index('is_verified');
            $table->index('is_active');
        });

        // Password reset tokens (kept for potential future use)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Laravel session table (needed when SESSION_DRIVER=database)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
