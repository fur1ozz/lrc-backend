<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\News;
use App\Models\Rally;
use Carbon\Carbon;

class NewsSeeder extends Seeder
{
    public function run()
    {

        if (!Storage::disk('public')->exists('news')) {
            Storage::disk('public')->makeDirectory('news');
        }

        $dummyImages = [
            'announcement.jpg',
            'day1-highlights.jpg',
            'results.jpg',
        ];

        foreach ($dummyImages as $image) {
            $sourcePath = database_path("seeders/images/news/{$image}");
            $targetPath = "news/{$image}";

            if (file_exists($sourcePath) && !Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
            }
        }

        $rallies = Rally::all();

        foreach ($rallies as $rally) {
            News::create([
                'pub_date_time' => Carbon::now(),
                'title' => 'Anticipation Builds for the Upcoming ' . $rally->rally_name . ' Rally',
                'paragraph' => 'Excitement is mounting as the ' . $rally->rally_name . ' rally approaches, promising intense competition and thrilling stages.',
                'body' => '<h2>Countdown to the ' . $rally->rally_name . ' Rally Begins!</h2>
                    <p>Motorsport fans are eagerly awaiting the upcoming ' . $rally->rally_name . ' rally, a cornerstone event known for its challenging terrain and fierce competition. This year&#39;s edition promises to deliver even more adrenaline-pumping action.</p>
                    <blockquote>&quot;We are ready to deliver the most exciting rally yet,&quot; said the event coordinator. &quot;Fans can expect surprises at every turn.&quot;</blockquote>
                    <p>Drivers from across the globe are preparing to tackle the unpredictable stages, which will test both speed and strategy. With new contenders joining the race, the competition is set to be tougher than ever.</p>
                    <ul>
                        <li>New challenging stages unveiled</li>
                        <li>Top drivers from multiple countries competing</li>
                        <li>Special fan zones for closer action</li>
                    </ul>',
                'img_src' => 'news/announcement.jpg',
                'rally_id' => $rally->id
            ]);

            News::create([
                'pub_date_time' => Carbon::now()->addDays(1),
                'title' => $rally->rally_name . ' Rally Day 1: Action-Packed Start!',
                'paragraph' => 'The first day of the ' . $rally->rally_name . ' rally saw intense rivalries and unexpected outcomes on the course.',
                'body' => '<h2>High-Speed Drama Unfolds on Day 1</h2>
                    <p>Day 1 of the ' . $rally->rally_name . ' rally delivered heart-stopping moments as drivers navigated treacherous routes and sudden weather changes. Several top contenders faced mechanical issues, shaking up the leaderboard early on.</p>
                    <p><strong>Highlights from Day 1 include:</strong></p>
                    <ol>
                        <li>A dramatic battle between last year&#39;s champion and a promising newcomer.</li>
                        <li>Unexpected weather conditions adding to the complexity of the stages.</li>
                        <li>Fans cheering on from designated areas despite the rain.</li>
                    </ol>
                    <p>As the rally progresses, teams will need to balance speed and caution to stay in the running for the ultimate prize.</p>',
                'img_src' => 'news/day1-highlights.jpg',
                'rally_id' => $rally->id
            ]);

            News::create([
                'pub_date_time' => Carbon::now()->addDays(2),
                'title' => 'Victors Crowned at the ' . $rally->rally_name . ' Rally Finale',
                'paragraph' => 'The ' . $rally->rally_name . ' rally concludes with a thrilling finish, crowning new champions after an intense competition.',
                'body' => '<h2>The ' . $rally->rally_name . ' Rally Ends with a Spectacular Finish</h2>
                    <p>After days of fierce competition, the ' . $rally->rally_name . ' rally has come to an electrifying conclusion. The final stage saw dramatic overtakes and emotional victories as drivers pushed their limits.</p>
                    <p>The winner, known for their precision driving and relentless pace, edged out competitors in a breathtaking showdown. Fans erupted in cheers as the podium ceremony celebrated the remarkable performances.</p>
                    <p><em>&quot;This victory means everything to us,&quot;</em> said the winning driver. &quot;The team worked tirelessly, and this is a triumph we will never forget.&quot;</p>
                    <p>The rally once again proved why it remains one of the most anticipated events in the motorsport calendar.</p>',
                'img_src' => 'news/results.jpg',
                'rally_id' => $rally->id
            ]);
        }
    }
}
