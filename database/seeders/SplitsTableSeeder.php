<?php

namespace Database\Seeders;

use App\Models\Split;
use App\Models\Stage;
use Illuminate\Database\Seeder;

class SplitsTableSeeder extends Seeder
{

    // TODO probably delete this file. This only seeds to te rally cesis
    public function run()
    {
        $stageIds = Stage::where('rally_id', 4)->pluck('id');

        foreach ($stageIds as $stageId) {
            $previousDistance = 0;

            for ($splitNumber = 1; $splitNumber <= 5; $splitNumber++) {
                $increment = round(mt_rand(20, 50) / 10, 1);
                $splitDistance = $previousDistance + $increment;

                Split::create([
                    'stage_id' => $stageId,
                    'split_number' => $splitNumber,
                    'split_distance' => $splitDistance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $previousDistance = $splitDistance;
            }
        }
    }
}
