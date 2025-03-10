<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        Season::create([
            'year' => '2024',
        ]);
        Season::create([
            'year' => '2025',
        ]);
    }
}
