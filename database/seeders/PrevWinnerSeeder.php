<?php

namespace Database\Seeders;

use App\Models\Rally;
use Illuminate\Database\Seeder;
use App\Models\PrevWinner;

class PrevWinnerSeeder extends Seeder
{
    public function run()
    {
        // TODO ADMIN PANEL create usable dummy winner images and seed them
        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            PrevWinner::create([
                'rally_id' => $rally->id,
                'crew_id' => rand(1, 50),
                'feedback' => $this->generateRandomFeedback(),
                'winning_img' => 'https://example.com/winning-image' . rand(1, 10) . '.jpg',
            ]);
        }
    }

    private function generateRandomFeedback()
    {
        $feedbacks = [
            'Great performance!',
            'Amazing drive!',
            'Outstanding effort!',
            'Incredible skill!',
            'Fantastic rally!',
            'Impressive speed!',
            'Perfect execution!',
            'Strong finish!',
            'An unforgettable race!',
        ];

        return $feedbacks[array_rand($feedbacks)];
    }
}
