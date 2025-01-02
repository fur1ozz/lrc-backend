<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Split;
use App\Models\SplitTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SplitTimesTableSeeder extends Seeder
{
    public function run()
    {
        SplitTime::truncate();

        $crews = Crew::where('rally_id', 4)->get();

        $splits = Split::all();

        foreach ($crews as $crew) {
            foreach ($splits as $split) {
                // Base time in seconds for split 1 (e.g., 2 minutes)
                $baseTime = 120; // 2 minutes in seconds

                // Increment time based on split number
                $increment = ($split->split_number - 1) * 150; // Add 150 seconds (2.5 minutes) per split

                // Add a small random variation to make it realistic
                $randomOffset = rand(-10, 30); // Random offset between -10 and +30 seconds

                // Final time in seconds
                $totalSeconds = $baseTime + $increment + $randomOffset;

                $milliseconds = round(rand(0, 999) / 10);

                $minutes = floor($totalSeconds / 60);
                $seconds = $totalSeconds % 60;

                $splitTime = sprintf('%d:%02d.%02d', $minutes, $seconds, $milliseconds);


                SplitTime::create([
                    'crew_id' => $crew->id,
                    'split_id' => $split->id,
                    'split_time' => $splitTime,
                ]);
            }
        }
    }
}
