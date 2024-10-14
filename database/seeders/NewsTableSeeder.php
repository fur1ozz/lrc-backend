<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\Rally;
use Carbon\Carbon;

class NewsTableSeeder extends Seeder
{
    public function run()
    {
        $rallies = Rally::all();

        foreach ($rallies as $rally) {
            News::create([
                'pub_date_time' => Carbon::now(),
                'title' => 'Exciting ' . $rally->rally_name . ' Rally Announcement',
                'paragraph' => 'The ' . $rally->rally_name . ' rally is set to be an exciting event this year with new challenges and exciting participants.',
                'img_src' => $rally->rally_tag . '-announcement.png',
                'rally_id' => $rally->id
            ]);

            News::create([
                'pub_date_time' => Carbon::now()->addDays(1),
                'title' => $rally->rally_name . ' Rally Day 1 Highlights',
                'paragraph' => 'Day 1 of ' . $rally->rally_name . ' has concluded with thrilling performances and unexpected turns on the road.',
                'img_src' => $rally->rally_tag . '-day1-highlights.png',
                'rally_id' => $rally->id
            ]);

            News::create([
                'pub_date_time' => Carbon::now()->addDays(2),
                'title' => 'Results of ' . $rally->rally_name . ' Rally',
                'paragraph' => 'The ' . $rally->rally_name . ' rally has concluded, and the winners have been announced. Stay tuned for detailed results and analysis.',
                'img_src' => $rally->rally_tag . '-results.png',
                'rally_id' => $rally->id
            ]);
        }
    }
}

