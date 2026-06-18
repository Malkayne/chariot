<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {

            $table->id();

            // ── Ownership ─────────────────────────────────────────
            $table->foreignId('driver_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // ── Route ─────────────────────────────────────────────
            $table->foreignId('from_zone_id')
                  ->constrained('zones')
                  ->onDelete('restrict');  // don't delete zones with rides

            $table->foreignId('to_zone_id')
                  ->constrained('zones')
                  ->onDelete('restrict');

            // Driver's GPS at time of posting (for map marker)
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();

            // ── Capacity ──────────────────────────────────────────
            $table->unsignedTinyInteger('total_seats');

            // available_seats starts = total_seats, decrements per accept
            $table->unsignedTinyInteger('available_seats');

            // ── State ─────────────────────────────────────────────
            // active    : driver posted, riders can request
            // full      : available_seats hit 0, no more requests
            // completed : driver marked trip as done
            // cancelled : driver or admin cancelled
            $table->enum('status', ['active', 'full', 'completed', 'cancelled'])
                  ->default('active');

            // Optional departure time displayed to riders
            $table->timestamp('departing_at')->nullable();

            // Optional free-text note from driver ("I'm by Gate A exit")
            $table->text('notes')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────
            $table->index('driver_id');
            $table->index('status');
            $table->index(['from_zone_id', 'to_zone_id']);
            $table->index('created_at');     // for admin "today's rides" query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
