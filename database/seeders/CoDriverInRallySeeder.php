<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoDriverInRallySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('co_driver_in_rallies')->insert([
            [
                'driver_id' => 1, // Assuming a driver with ID 1 exists
                'co_driver_id' => 2, // Assuming a co-driver with ID 2 exists
                'rally_id' => 1, // Assuming a rally with ID 1 exists
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_id' => 3, // Assuming a driver with ID 3 exists
                'co_driver_id' => 2, // Assuming a co-driver with ID 4 exists
                'rally_id' => 2, // Assuming a rally with ID 2 exists
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_id' => 1, // Assuming a driver with ID 5 exists
                'co_driver_id' => 2, // Assuming a co-driver with ID 6 exists
                'rally_id' => 3, // Assuming a rally with ID 3 exists
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more records as needed
        ]);
    }
}
