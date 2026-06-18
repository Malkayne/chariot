<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {

            $table->id();

            // ── Identity ──────────────────────────────────────────
            // name       : human-readable e.g. "Main Auditorium"
            // short_code : uppercase slug  e.g. "MAIN_AUD"
            $table->string('name', 100);
            $table->string('short_code', 20)->unique();

            // ── Coordinates ───────────────────────────────────────
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // ── Meta ──────────────────────────────────────────────
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
