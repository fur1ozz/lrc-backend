<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Rally;
use Illuminate\Database\Seeder;
use App\Models\PrevWinner;
use Illuminate\Support\Facades\Storage;

class PrevWinnerSeeder extends Seeder
{
    public function run()
    {
        if (!Storage::disk('public')->exists('rally_winners')) {
            Storage::disk('public')->makeDirectory('rally_winners');
        }

        $dummyImages = [
            'winner-1.jpg',
        ];

        foreach ($dummyImages as $image) {
            $sourcePath = database_path("seeders/images/rally_winners/{$image}");
            $targetPath = "rally_winners/{$image}";

            if (file_exists($sourcePath) && !Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
            }
        }

        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            $crew = Crew::where('rally_id', $rally->id)->inRandomOrder()->first();

            if ($crew) {
                PrevWinner::create([
                    'rally_id' => $rally->id,
                    'crew_id' => $crew->id,
                    'feedback' => $this->generateRandomFeedback(),
                    'winning_img' => 'rally_winners/winner-1.jpg',
                ]);
            }
        }
    }

    private function generateRandomFeedback()
    {
        $feedbacks = [
            'An outstanding display of teamwork and precision, leading to a well-deserved victory.',
            'A thrilling performance from start to finish, showcasing their exceptional driving skills.',
            'This team delivered an impeccable rally, leaving no room for their competitors to catch up.',
            'A masterclass in rally racing, combining strategy, speed, and skill for a dominant win.',
            'From the first stage to the last, their performance was nothing short of spectacular.',
            'A flawless execution on every turn secured them the top spot on the podium.',
            'An epic battle on the rally track, ending with a triumphant and well-earned victory.',
            'Their consistency and precision throughout the race led to an unforgettable triumph.',
            'A breathtaking rally, with this team demonstrating unparalleled expertise and determination.',
        ];

        return $feedbacks[array_rand($feedbacks)];
    }
}
