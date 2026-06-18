<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {

            $table->id();

            // ── Owner ─────────────────────────────────────────────
            $table->foreignId('user_id')
                  ->unique()                    // one profile per driver
                  ->constrained('users')
                  ->onDelete('cascade');

            // ── Vehicle details ───────────────────────────────────
            // vehicle_type : car | bus | keke | sienna | hiace | other
            $table->string('vehicle_type', 30);
            $table->string('vehicle_model', 80);       // e.g. "Toyota Camry 2018"
            $table->string('vehicle_color', 40);
            $table->string('plate_number', 20)->unique();

            // total_seats = passenger capacity (excluding driver)
            $table->unsignedTinyInteger('total_seats');

            // ── Live location ─────────────────────────────────────
            // Precision: 10 digits, 7 decimal places ≈ 1cm accuracy
            // Null when driver has never shared location or is offline
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->timestamp('location_updated_at')->nullable();

            // ── Availability state ────────────────────────────────
            $table->boolean('is_available')->default(false);
            $table->enum('status', ['offline', 'available', 'on_trip'])
                  ->default('offline');

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────
            $table->index('status');
            $table->index('is_available');
            $table->index(['current_lat', 'current_lng']); // spatial proximity queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
