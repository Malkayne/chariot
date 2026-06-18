<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            // ── Recipient ─────────────────────────────────────────
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // ── Content ───────────────────────────────────────────
            $table->string('type', 50); // ride_request | ride_accepted | etc.
            $table->string('title', 120);
            $table->text('body');

            // Extra context for front-end deep links
            // e.g. {"ride_id": 42, "request_id": 17}
            $table->json('data')->nullable();

            // ── State ─────────────────────────────────────────────
            $table->boolean('is_read')->default(false);

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────
            $table->index(['user_id', 'is_read']);  // unread count query
            $table->index('created_at');             // chronological drawer list
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
