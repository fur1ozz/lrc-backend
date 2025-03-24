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
            'Jump start at SS-{stage}' => '0:10.00',
            '1 Minute late at TC-{stage}' => '0:10.00',
            '3 Minutes late at TC-{stage}' => '0:30.00',
            '5 Minutes late at TC-{stage}' => '0:50.00',
            '8 Minutes late at TC-{stage}' => '1:20.00',
            'Stewards decision' => '0:20.00',
            'Exceeding speed limit in service park' => '0:40.00',
            'Unauthorized service assistance' => '1:00.00',
            'Failure to follow official route' => '1:30.00',
            'Missing time control' => '2:00.00',
            'Illegal tire change' => '0:15.00',
            'Failure to wear safety equipment' => '0:25.00',
            'Exceeding max service time' => '0:45.00',
            'Late arrival at parc fermé' => '0:35.00',
            'Missed driver briefing' => '0:20.00',
            'Improper refueling procedure' => '0:50.00',
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
