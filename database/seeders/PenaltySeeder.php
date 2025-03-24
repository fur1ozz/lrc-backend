<?php

namespace Database\Seeders;

use App\Models\Rally;
use App\Models\Stage;
use Illuminate\Database\Seeder;
use App\Models\Crew;
use App\Models\Penalties;

class PenaltySeeder extends Seeder
{
    public function run()
    {
        $penalties = [
            'Jump start at SS-{stage}' => 10000, // 0:10.00 → 10000 ms
            '1 Minute late at TC-{stage}' => 10000, // 0:10.00 → 10000 ms
            '3 Minutes late at TC-{stage}' => 30000, // 0:30.00 → 30000 ms
            '5 Minutes late at TC-{stage}' => 50000, // 0:50.00 → 50000 ms
            '8 Minutes late at TC-{stage}' => 80000, // 1:20.00 → 80000 ms
            'Stewards decision' => 20000, // 0:20.00 → 20000 ms
            'Exceeding speed limit in service park' => 40000, // 0:40.00 → 40000 ms
            'Unauthorized service assistance' => 60000, // 1:00.00 → 60000 ms
            'Failure to follow official route' => 90000, // 1:30.00 → 90000 ms
            'Missing time control' => 120000, // 2:00.00 → 120000 ms
            'Illegal tire change' => 15000, // 0:15.00 → 15000 ms
            'Failure to wear safety equipment' => 25000, // 0:25.00 → 25000 ms
            'Exceeding max service time' => 45000, // 0:45.00 → 45000 ms
            'Late arrival at parc fermé' => 35000, // 0:35.00 → 35000 ms
            'Missed driver briefing' => 20000, // 0:20.00 → 20000 ms
            'Improper refueling procedure' => 50000, // 0:50.00 → 50000 ms
        ];

        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            $stages = Stage::where('rally_id', $rally->id)->get();
            $crews = Crew::where('rally_id', $rally->id)->inRandomOrder()->limit(20)->get();

            foreach ($crews as $crew) {
                $penaltyKey = array_rand($penalties);
                $penaltyAmount = $penalties[$penaltyKey];

                $stage = $stages->random();
                $penaltyType = str_replace('{stage}', $stage->stage_number, $penaltyKey);

                Penalties::create([
                    'crew_id' => $crew->id,
                    'stage_id' => $stage->id,
                    'penalty_type' => $penaltyType,
                    'penalty_amount' => $penaltyAmount,
                ]);
            }
        }
    }
}
