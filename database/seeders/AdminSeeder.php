<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin account
        $admin = User::firstOrCreate(
            ['phone' => '08000000001'],
            [
                'name'        => 'Chariot Admin',
                'email'       => 'admin@chariot.camp',
                'password'    => Hash::make('admin1234'),
                'role'        => 'admin',
                'is_verified' => true,
                'is_active'   => true,
            ]
        );

        $this->command->info("Admin account ready: phone=08000000001 / password=admin1234");

        // Create a test rider
        User::firstOrCreate(
            ['phone' => '08000000002'],
            [
                'name'        => 'Test Rider',
                'email'       => 'rider@chariot.camp',
                'password'    => Hash::make('password'),
                'role'        => 'rider',
                'is_verified' => true,
                'is_active'   => true,
            ]
        );

        // Create a test driver with profile
        $driver = User::firstOrCreate(
            ['phone' => '08000000003'],
            [
                'name'        => 'Test Driver',
                'email'       => 'driver@chariot.camp',
                'password'    => Hash::make('password'),
                'role'        => 'driver',
                'is_verified' => true,
                'is_active'   => true,
            ]
        );

        if (! $driver->driverProfile) {
            $driver->driverProfile()->create([
                'vehicle_type'  => 'car',
                'vehicle_model' => 'Toyota Camry 2020',
                'vehicle_color' => 'Silver',
                'plate_number'  => 'ABC-123-XY',
                'total_seats'   => 4,
                'status'        => 'offline',
                'is_available'  => false,
            ]);
        }

        $this->command->info("Test accounts created: rider@chariot.camp, driver@chariot.camp (password: password)");
    }
}
