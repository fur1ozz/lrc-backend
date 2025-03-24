<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // static data
            SeasonSeeder::class,
            GroupsClassesSeeder::class,

            RallySeeder::class,
            RallyGroupClassSeeder::class,
            StageSeeder::class,
            SplitSeeder::class,

            CrewParticipantTeamSeeder::class,
            CrewParticipantTeamHistoricSeeder::class,
            NewsSeeder::class,
            FoldersAndDocumentsSeeder::class,

            // other
            PrevWinnerSeeder::class,

            // Results
            RetirementSeeder::class,
        ]);
    }
}
