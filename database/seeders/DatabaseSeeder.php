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

            ChampionshipClassSeeder::class,

            RallySeeder::class,
            RallyGroupClassSeeder::class,
            StageSeeder::class,
            SplitSeeder::class,

//            CrewParticipantTeamSeeder::class,
//            CrewParticipantTeamHistoricSeeder::class,
            RealisticCrewSeeder::class,
            RealisticCrewHistoricSeeder::class,
            ExtendedCrewParticipationSeeder::class,

            StartTimeSeeder::class,
            NewsSeeder::class,
            FoldersAndDocumentsSeeder::class,
            GalleryImagesSeeder::class,
            SponsorsSeeder::class,
            RallySponsorsSeeder::class,

            // other
            PrevWinnerSeeder::class,

            // Results (2024 season only)
            RetirementSeeder::class,
            PenaltySeeder::class,
            StageResultsSeeder::class,
            SplitTimesSeeder::class,
            OverallResultsSeeder::class,

            // user
            UserSeeder::class,
        ]);
    }
}
