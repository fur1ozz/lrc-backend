<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
        $this->call([
            // static data
            SeasonSeeder::class,
            GroupsClassesSeeder::class,

            RallySeeder::class,
            RallyGroupClassSeeder::class,
            StageSeeder::class,

            CrewParticipantTeamSeeder::class,
            NewsTableSeeder::class,
            FoldersAndDocumentsSeeder::class,

            // other
            PrevWinnerSeeder::class,
        ]);
    }
}
