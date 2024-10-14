<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PrevWinner;

class PrevWinnerSeeder extends Seeder
{
    public function run()
    {
        PrevWinner::create([
            'rally_id' => 1,
            'crew_id' => 1,
            'feedback' => 'Great performance!',
            'winning_img' => 'https://example.com/winning-image1.jpg'
        ]);

        PrevWinner::create([
            'rally_id' => 2,
            'crew_id' => 2,
            'feedback' => 'Amazing drive!',
            'winning_img' => 'https://example.com/winning-image2.jpg'
        ]);

    }
}
