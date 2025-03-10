<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            SeasonSeeder::class,
            RallySeeder::class,
            NewsTableSeeder::class,
            StageSeeder::class,
            FoldersAndDocumentsSeeder::class,
            TeamSeeder::class,
            ParticipantSeeder::class,
            CrewSeeder::class,
            PrevWinnerSeeder::class,
        ]);
    }
}
