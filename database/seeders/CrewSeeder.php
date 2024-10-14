<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Crew;

class CrewSeeder extends Seeder
{
    public function run()
    {
        Crew::create([
            'driver_id' => 1, // Assuming there's a participant with id 1
            'team_id' => 1,   // Assuming there's a team with id 1
            'rally_id' => 1,  // Assuming there's a rally with id 1
            'crew_number' => '1',
            'car' => 'Subaru Impreza',
            'drive_type' => 'AWD',
            'drive_class' => 'RC2',
        ]);

        Crew::create([
            'driver_id' => 2, // Assuming there's a participant with id 2
            'team_id' => 2,   // Assuming there's a team with id 2
            'rally_id' => 2,  // Assuming there's a rally with id 2
            'crew_number' => '2',
            'car' => 'Ford Fiesta R5',
            'drive_type' => 'AWD',
            'drive_class' => 'RC2',
        ]);

        Crew::create([
            'driver_id' => 3, // Assuming there's a participant with id 3
            'team_id' => 1,   // Assuming there's a team with id 1
            'rally_id' => 3,  // Assuming there's a rally with id 3
            'crew_number' => '3',
            'car' => 'Citroen C3 WRC',
            'drive_type' => 'AWD',
            'drive_class' => 'WRC',
        ]);
    }
}