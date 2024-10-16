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
        $stagesData = [
            [
                'stage_name' => 'CĒSIS',
                'stage_number' => 1,
                'distance_km' => 8.11,
            ],
            [
                'stage_name' => 'MEŽA APSAIMNIEKOTĀJS',
                'stage_number' => 2,
                'distance_km' => 13.63,
            ],
            [
                'stage_name' => 'MELAT-LUX',
                'stage_number' => 3,
                'distance_km' => 9.42,
            ],
            [
                'stage_name' => 'SARSAUTO',
                'stage_number' => 4,
                'distance_km' => 13.52,
            ],
            [
                'stage_name' => 'EUROPCAR',
                'stage_number' => 5,
                'distance_km' => 14.72,
            ],
            [
                'stage_name' => 'PRINTMII',
                'stage_number' => 6,
                'distance_km' => 9.42,
            ],
            [
                'stage_name' => 'METĀLU PASAULE',
                'stage_number' => 7,
                'distance_km' => 13.52,
            ],
            [
                'stage_name' => 'TOLMETS VIDZEME',
                'stage_number' => 8,
                'distance_km' => 14.72,
            ]
        ];

        $rally = Rally::find(4);

        if ($rally) {
            foreach ($stagesData as $stageData) {
                Stage::create([
                    'rally_id' => $rally->id,
                    'stage_name' => $stageData['stage_name'],
                    'stage_number' => $stageData['stage_number'],
                    'distance_km' => $stageData['distance_km'],
                    'start_date' => Carbon::now()->addDays($stageData['stage_number']),
                    'start_time' => Carbon::now()->addHours($stageData['stage_number'])->format('H:i:s'),
                ]);
            }
        } else {
            echo "Rally with ID 4 not found.";
        }



//        $rallies = Rally::all();
//
//        foreach ($rallies as $rally) {
//            for ($i = 1; $i <= 3; $i++) {
//                Stage::create([
//                    'rally_id' => $rally->id,
//                    'stage_name' => 'Stage ' . $i . ' of ' . $rally->rally_name,
//                    'stage_number' => $i,
//                    'distance_km' => rand(5, 20),
//                    'start_date' => Carbon::now()->addDays($i),
//                    'start_time' => Carbon::now()->addHours($i)->format('H:i:s'),
//                ]);
//            }
//        }
    }
}

