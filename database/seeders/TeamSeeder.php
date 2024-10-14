<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    public function run()
    {
        Team::create([
            'team_name' => 'Team Alpha',
            'manager_name' => 'John Doe',
            'manager_contact' => 'john@example.com',
        ]);

        Team::create([
            'team_name' => 'Team Bravo',
            'manager_name' => 'Jane Smith',
            'manager_contact' => 'jane@example.com',
        ]);

        Team::create([
            'team_name' => 'Team Charlie',
            'manager_name' => 'Bill Gates',
            'manager_contact' => 'bill@example.com',
        ]);
    }
}

