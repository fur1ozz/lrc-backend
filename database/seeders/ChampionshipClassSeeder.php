<?php

namespace Database\Seeders;

use App\Models\ChampionshipClass;
use App\Models\Season;
use Illuminate\Database\Seeder;

class ChampionshipClassSeeder extends Seeder
{
    public function run()
    {
        $seasons = Season::all();

        $classIds = array_merge(range(1, 8), range(20, 22), range(23, 25));

        foreach ($seasons as $season) {
            foreach ($classIds as $classId) {
                ChampionshipClass::create([
                    'season_id' => $season->id,
                    'class_id' => $classId,
                ]);
            }
        }

    }
}
