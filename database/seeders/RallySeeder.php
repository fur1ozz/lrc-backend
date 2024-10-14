<?php
namespace Database\Seeders;

use App\Models\Rally;
use App\Models\Season;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RallySeeder extends Seeder
{
    public function run()
    {
        $season = Season::where('year', 2024)->firstOrFail();

        $rallies = [
            [
                'rally_name' => 'Rally Alūksne',
                'rally_tag' => 'rally-aluksne',
                'date_from' => '2024-01-19',
                'date_to' => '2024-01-20',
                'location' => 'lv',
                'road_surface' => 'snow',
                'rally_sequence' => 1,
            ],
            [
                'rally_name' => 'Rally Sarma',
                'rally_tag' => 'rally-sarma',
                'date_from' => '2024-02-10',
                'date_to' => '2024-02-10',
                'location' => 'lv',
                'road_surface' => 'snow',
                'rally_sequence' => 2,
            ],
            [
                'rally_name' => 'Delfi Rally Estonia',
                'rally_tag' => 'delfi-rally-estonia',
                'date_from' => '2024-07-05',
                'date_to' => '2024-07-07',
                'location' => 'ee',
                'road_surface' => 'gravel',
                'rally_sequence' => 3,
            ],
            [
                'rally_name' => 'Rally Cēsis',
                'rally_tag' => 'rally-cesis',
                'date_from' => '2024-08-09',
                'date_to' => '2024-08-10',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_sequence' => 4,
            ],
            [
                'rally_name' => 'Rally Paide',
                'rally_tag' => 'rally-paide',
                'date_from' => '2024-08-23',
                'date_to' => '2024-08-24',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_sequence' => 5,
            ],
            [
                'rally_name' => 'Samsonas Rally Utena',
                'rally_tag' => 'samsonas-rally-utena',
                'date_from' => '2024-09-26',
                'date_to' => '2024-09-28',
                'location' => 'lt',
                'road_surface' => 'gravel',
                'rally_sequence' => 6,
            ],
            [
                'rally_name' => 'Rallysprint Latvija',
                'rally_tag' => 'rally-sprint-latvia',
                'date_from' => '2024-10-26',
                'date_to' => '2024-10-27',
                'location' => 'lv',
                'road_surface' => 'tarmac',
                'rally_sequence' => 7,
            ],
        ];

        foreach ($rallies as $rallyData) {
            Rally::create(array_merge($rallyData, ['season_id' => $season->id]));
        }
    }
}
