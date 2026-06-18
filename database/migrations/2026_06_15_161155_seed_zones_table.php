<?php

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
