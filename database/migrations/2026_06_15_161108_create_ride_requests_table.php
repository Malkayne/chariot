<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_requests', function (Blueprint $table) {

            $table->id();

            // ── Relations ─────────────────────────────────────────
            $table->foreignId('ride_id')
                  ->constrained('rides')
                  ->onDelete('cascade');   // delete requests when ride deleted

            $table->foreignId('rider_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // ── State ─────────────────────────────────────────────
            $table->enum('status', [
                'pending',    // waiting for driver response
                'accepted',   // driver said yes
                'declined',   // driver said no
                'cancelled',  // rider withdrew
                'completed',  // ride completed, rider was on board
            ])->default('pending');

            // ── Details ───────────────────────────────────────────
            $table->text('pickup_note')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────
            $table->index('rider_id');
            $table->index('ride_id');
            $table->index('status');
            $table->index(['ride_id', 'status']);  // "pending requests for this ride"
            $table->index(['rider_id', 'status']); // "my current requests"

            // Prevent duplicate active requests from same rider on same ride
            $table->unique(['ride_id', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_requests');
    }
};
