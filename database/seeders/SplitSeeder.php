<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SplitSeeder extends Seeder
{
    public function run()
    {
        $stages = DB::table('stages')->get(['id', 'distance_km']);

        foreach ($stages as $stage) {
            $splitCount = $this->determineSplitCount($stage->distance_km);

            $lastSplitDistance = 0;

            for ($i = 1; $i <= $splitCount; $i++) {
                if ($i == 1) {
                    $splitDistance = round($stage->distance_km * 0.2, 1);
                } else {
                    $remainingDistance = $stage->distance_km * 0.9 - $lastSplitDistance;
                    $increment = $remainingDistance / ($splitCount - $i + 1);
                    $splitDistance = $lastSplitDistance + round($increment, 1);
                }

                $splitDistance = min($splitDistance, $stage->distance_km);

                DB::table('splits')->insert([
                    'stage_id' => $stage->id,
                    'split_number' => $i,
                    'split_distance' => $splitDistance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lastSplitDistance = $splitDistance;
            }
        }
    }

    private function determineSplitCount($distance): int
    {
        if ($distance <= 5) {
            return 1;
        } elseif ($distance <= 10) {
            return 2;
        } elseif ($distance <= 20) {
            return 3;
        } elseif ($distance <= 30) {
            return 4;
        } else {
            return 5;
        }
    }
}
