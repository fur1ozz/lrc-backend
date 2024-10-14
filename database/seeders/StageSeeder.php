<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stage;
use App\Models\Rally;
use Carbon\Carbon;

class StageSeeder extends Seeder
{
    public function run()
    {
        $rallies = Rally::all();

        foreach ($rallies as $rally) {
            for ($i = 1; $i <= 3; $i++) {
                Stage::create([
                    'rally_id' => $rally->id,
                    'stage_name' => 'Stage ' . $i . ' of ' . $rally->rally_name,
                    'stage_number' => $i,
                    'distance_km' => rand(5, 20),
                    'start_date' => Carbon::now()->addDays($i),
                    'start_time' => Carbon::now()->addHours($i)->format('H:i:s'),
                ]);
            }
        }
    }
}

