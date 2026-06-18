<?php

/*
|=======================================================================
| CHARIOT — ALL MIGRATIONS IN ONE FILE
| Cut at the marked dividers into individual migration files.
|=======================================================================
| FILE MAP
| ─────────────────────────────────────────────────────────────────────
| [1] database/migrations/2024_01_01_000001_create_users_table.php
| [2] database/migrations/2024_01_01_000002_create_driver_profiles_table.php
| [3] database/migrations/2024_01_01_000003_create_zones_table.php
| [4] database/migrations/2024_01_01_000004_create_rides_table.php
| [5] database/migrations/2024_01_01_000005_create_ride_requests_table.php
| [6] database/migrations/2024_01_01_000006_create_notifications_table.php
| [7] database/migrations/2024_01_01_000007_create_personal_access_tokens_table.php
| [8] database/migrations/2024_01_01_000008_create_jobs_table.php
| [9] database/migrations/2024_01_01_000009_seed_zones_table.php
|=======================================================================
| RUN ORDER: 1 → 2 → 3 → 4 → 5 → 6 → 7 → 8 → 9
| COMMAND  : php artisan migrate --seed
|=======================================================================
*/

// ===================================================================
// [1]  2024_01_01_000001_create_users_table.php
//
// Core users table. Replaces Laravel's default users migration.
// Stores riders, drivers and admins in one table (role-based).
// Phone is the primary login identifier. Email is optional (OTP).
// OTP fields are on this table — no separate table needed for MVP.
// ===================================================================
// ======================== [1] START ================================

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

// ========================= [1] END =================================




// ===================================================================
// [2]  2024_01_01_000002_create_driver_profiles_table.php
//
// One-to-one extension of users where role = 'driver'.
// Stores vehicle details + live GPS + availability status.
// Location is updated via POST /api/driver/location every 8s.
// status enum drives the driver marker colour on the map:
//   offline   → grey marker
//   available → gold marker  (broadcasting location)
//   on_trip   → green marker (has active ride with accepted riders)
// ===================================================================
// ======================== [2] START ================================

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

// ========================= [2] END =================================




// ===================================================================
// [3]  2024_01_01_000003_create_zones_table.php
//
// Camp ground landmarks / pickup zones.
// Seeded with default RCCG Camp Ground zones (see migration [9]).
// Riders select from → to zone when filtering rides.
// Drivers select from → to when posting a ride.
// Admin can add, edit, deactivate zones from the admin panel.
// ===================================================================
// ======================== [3] START ================================

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

// ========================= [3] END =================================




// ===================================================================
// [4]  2024_01_01_000004_create_rides_table.php
//
// A ride = one trip posted by a driver.
// Lifecycle:  active → full (seats exhausted) → completed | cancelled
// A driver can only have one active/full ride at a time
// (enforced at application layer, not DB constraint for simplicity).
// pickup_lat/lng is the driver's current GPS at time of posting.
// available_seats decrements on each accepted request.
// ===================================================================
// ======================== [4] START ================================

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

// ========================= [4] END =================================




// ===================================================================
// [5]  2024_01_01_000005_create_ride_requests_table.php
//
// A ride_request = one rider asking to join one ride.
// Lifecycle: pending → accepted | declined | cancelled
//            accepted → completed (when driver completes the ride)
// One rider can have at most one pending/accepted request per ride
// (enforced at application layer via unique scope check).
// pickup_note is an optional message rider sends to driver
// e.g. "I'm standing near the fountain, wearing red cap".
// responded_at is set when driver accepts or declines.
// ===================================================================
// ======================== [5] START ================================

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

// ========================= [5] END =================================




// ===================================================================
// [6]  2024_01_01_000006_create_notifications_table.php
//
// Lightweight custom notifications table — avoids Laravel's
// polymorphic notifications table overhead for this MVP.
// type values drive the icon shown in the notification drawer:
//   ride_request  → bell icon (for drivers)
//   ride_accepted → check icon (for riders)
//   ride_declined → x icon (for riders)
//   ride_cancelled→ warning icon
//   admin_message → info icon
// data JSON column carries entity IDs for deep-linking.
// ===================================================================
// ======================== [6] START ================================

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

// ========================= [6] END =================================




// ===================================================================
// [7]  2024_01_01_000007_create_personal_access_tokens_table.php
//
// Required by Laravel Sanctum for API token authentication.
// Tokens are issued on login and embedded in Blade meta tags
// so that chariot.js can make authenticated API fetch() calls
// without a separate SPA login flow.
// ===================================================================
// ======================== [7] START ================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');           // polymorphic to User
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

// ========================= [7] END =================================




// ===================================================================
// [8]  2024_01_01_000008_create_jobs_table.php
//
// Required for QUEUE_CONNECTION=database (no Redis needed for MVP).
// Used by: SendOtpEmail job, NotifyDriverOfRequest job.
// Also creates the failed_jobs table for debugging queue failures.
// Run the worker with: php artisan queue:work --tries=3
// ===================================================================
// ======================== [8] START ================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Jobs queue ────────────────────────────────────────────
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // ── Batched jobs (optional but good practice) ─────────────
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // ── Failed jobs ───────────────────────────────────────────
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // ── Cache (for rate-limiting without Redis) ───────────────
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
    }
};

// ========================= [8] END =================================




// ===================================================================
// [9]  2024_01_01_000009_seed_zones_table.php
//
// Not a true migration — this is a seeder disguised as a migration
// so that `php artisan migrate` populates the zones table immediately.
// For a real deployment, move these to database/seeders/ZoneSeeder.php
// and call it from DatabaseSeeder. For hackathon: this is fine.
//
// Coordinates below are approximate RCCG Camp Ground, KM 46, Lagos-Ibadan
// Expressway, Ogun State, Nigeria (6.8922°N, 3.7186°E ± local offsets).
// Replace with surveyed coordinates once on-site.
// ===================================================================
// ======================== [9] START ================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        DB::table('zones')->insert([

            // ── Main worship areas ────────────────────────────────
            [
                'name'        => 'Main Auditorium',
                'short_code'  => 'MAIN_AUD',
                'lat'         => 6.892200,
                'lng'         => 3.718600,
                'description' => 'The primary convention centre — central point of camp.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Holy Ghost Arena',
                'short_code'  => 'HG_ARENA',
                'lat'         => 6.891500,
                'lng'         => 3.719200,
                'description' => 'Open-air arena for large programmes and night vigils.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Redemption City Chapel',
                'short_code'  => 'CITY_CHAP',
                'lat'         => 6.893000,
                'lng'         => 3.717800,
                'description' => 'City of David Chapel — smaller gatherings and services.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // ── Gates ─────────────────────────────────────────────
            [
                'name'        => 'Gate A (Main Entrance)',
                'short_code'  => 'GATE_A',
                'lat'         => 6.890000,
                'lng'         => 3.715000,
                'description' => 'Primary entrance from Lagos-Ibadan Expressway.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Gate B (North)',
                'short_code'  => 'GATE_B',
                'lat'         => 6.895000,
                'lng'         => 3.716500,
                'description' => 'North gate — access to residential zones.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Gate C (South)',
                'short_code'  => 'GATE_C',
                'lat'         => 6.889500,
                'lng'         => 3.720000,
                'description' => 'South gate — access to secondary roads.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // ── Zones / Parishes ──────────────────────────────────
            [
                'name'        => 'Holy Ghost Zone',
                'short_code'  => 'HG_ZONE',
                'lat'         => 6.893800,
                'lng'         => 3.720100,
                'description' => 'North-east residential and programme zone.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Canaan Land Zone',
                'short_code'  => 'CANAAN',
                'lat'         => 6.891000,
                'lng'         => 3.721500,
                'description' => 'East-side accommodation and dining zone.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Joshua Zone',
                'short_code'  => 'JOSHUA',
                'lat'         => 6.894500,
                'lng'         => 3.717000,
                'description' => 'North-west zone with RCCG offices.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Elijah Zone',
                'short_code'  => 'ELIJAH',
                'lat'         => 6.890800,
                'lng'         => 3.716200,
                'description' => 'West-side zone near parking areas.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Moses Zone',
                'short_code'  => 'MOSES',
                'lat'         => 6.892700,
                'lng'         => 3.722000,
                'description' => 'Far east residential accommodation area.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // ── Amenities & Services ──────────────────────────────
            [
                'name'        => 'Medical Centre',
                'short_code'  => 'MEDICAL',
                'lat'         => 6.892100,
                'lng'         => 3.719800,
                'description' => 'Camp ground hospital and first-aid centre.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Camp Market',
                'short_code'  => 'MARKET',
                'lat'         => 6.891300,
                'lng'         => 3.718000,
                'description' => 'Main shopping and food market on camp.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Tabernacle Food Court',
                'short_code'  => 'FOOD_CT',
                'lat'         => 6.892500,
                'lng'         => 3.717500,
                'description' => 'Central dining and food vendor area.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'RCCG Secretariat',
                'short_code'  => 'SECRETARIAT',
                'lat'         => 6.893500,
                'lng'         => 3.718900,
                'description' => 'Main administrative offices of RCCG.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

            // ── Transport hubs ────────────────────────────────────
            [
                'name'        => 'Main Parking Lot',
                'short_code'  => 'PARKING',
                'lat'         => 6.889800,
                'lng'         => 3.716800,
                'description' => 'Primary vehicle parking area near Gate A.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'North Parking',
                'short_code'  => 'PARK_N',
                'lat'         => 6.895500,
                'lng'         => 3.718000,
                'description' => 'Overflow parking — north end of camp.',
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],

        ]);
    }

    public function down(): void
    {
        DB::table('zones')->truncate();
    }
};

// ========================= [9] END =================================
