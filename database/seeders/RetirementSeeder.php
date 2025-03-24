<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Rally;
use App\Models\Retirement;
use App\Models\Stage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RetirementSeeder extends Seeder
{
    public function run()
    {
        $retirementReasons = [
            'Technical', 'Engine', 'Roll over', 'Out of road',
            'Fuel pump', 'Fire', 'Wheel', 'Axle', 'DNF'
        ];

        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            $crews = Crew::where('rally_id', $rally->id)->inRandomOrder()->limit(10)->get();

            $stageCount = Stage::where('rally_id', $rally->id)->count();

            foreach ($crews as $crew) {
                $randomReason = $retirementReasons[array_rand($retirementReasons)];
                $randomStage = rand(1, $stageCount);

                Retirement::create([
                    'crew_id' => $crew->id,
                    'rally_id' => $rally->id,
                    'retirement_reason' => $randomReason,
                    'stage_of_retirement' => $randomStage,
                ]);
            }
        }
    }
}
