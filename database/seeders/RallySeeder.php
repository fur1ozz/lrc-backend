<?php
namespace Database\Seeders;

use App\Models\Rally;
use App\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RallySeeder extends Seeder
{
    public function run()
    {
        if (!Storage::disk('public')->exists('rally_images')) {
            Storage::disk('public')->makeDirectory('rally_images');
        }

        $rallyImages = [
            'rally-aluksne-2024.png',
            'rally-sarma-2024.png',
            'delfi-rally-estonia-2024.png',
            'rally-cesis-2024.png',
            'rally-paide-2024.png',
            'samsonas-rally-utena-2024.png',
            'rally-sprint-latvia-2024.png',
            'rally-aluksne-2025.png',
            'rally-sarma-2025.png',
            'rally-sprint-vidzeme-2025.png',
            'rally-sprint-talsi-2025.png',
            'rally-louna-eesti-2025.png',
            'rally-paide-2025.png',
            'rally-cesis-2025.png',
            'rally-sprint-latvia-2025.png',
        ];

        foreach ($rallyImages as $image) {
            $sourcePath = database_path("seeders/images/rally_images/{$image}");
            $targetPath = "rally_images/{$image}";

            if (file_exists($sourcePath) && !Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
            }
        }

        // Seed 2024 season
        $season2024 = Season::where('year', 2024)->firstOrFail();

        $rallies2024 = [
            [
                'rally_name' => 'Rally Alūksne',
                'rally_tag' => 'rally-aluksne',
                'date_from' => '2024-01-19',
                'date_to' => '2024-01-20',
                'location' => 'lv',
                'road_surface' => 'snow',
                'rally_img' => 'rally_images/rally-aluksne-2024.png',
            ],
            [
                'rally_name' => 'Rally Sarma',
                'rally_tag' => 'rally-sarma',
                'date_from' => '2024-02-10',
                'date_to' => '2024-02-10',
                'location' => 'lv',
                'road_surface' => 'snow',
                'rally_img' => 'rally_images/rally-sarma-2024.png',
            ],
            [
                'rally_name' => 'Delfi Rally Estonia',
                'rally_tag' => 'delfi-rally-estonia',
                'date_from' => '2024-07-05',
                'date_to' => '2024-07-07',
                'location' => 'ee',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/delf-rally-estonia-2024.png',
            ],
            [
                'rally_name' => 'Rally Cēsis',
                'rally_tag' => 'rally-cesis',
                'date_from' => '2024-08-09',
                'date_to' => '2024-08-10',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/rally-cesis-2024.png',
            ],
            [
                'rally_name' => 'Rally Paide',
                'rally_tag' => 'rally-paide',
                'date_from' => '2024-08-23',
                'date_to' => '2024-08-24',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/rally-paide-2024.png',
            ],
            [
                'rally_name' => 'Samsonas Rally Utena',
                'rally_tag' => 'samsonas-rally-utena',
                'date_from' => '2024-09-26',
                'date_to' => '2024-09-28',
                'location' => 'lt',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/samsonas-rally-utena-2024.png',
            ],
            [
                'rally_name' => 'Rallysprint Latvija',
                'rally_tag' => 'rally-sprint-latvia',
                'date_from' => '2024-10-26',
                'date_to' => '2024-10-27',
                'location' => 'lv',
                'road_surface' => 'tarmac',
                'rally_img' => 'rally_images/rally-sprint-latvia-2024.png',
            ],
        ];

        foreach ($rallies2024 as $rallyData) {
            Rally::create(array_merge($rallyData, ['season_id' => $season2024->id]));
        }

        // Seed 2025 season
        $season2025 = Season::where('year', 2025)->firstOrFail();

        $rallies2025 = [
            [
                'rally_name' => 'Rallijs Alūksne',
                'rally_tag' => 'rally-aluksne',
                'date_from' => '2025-01-10',
                'date_to' => '2025-01-11',
                'location' => 'lv',
                'road_surface' => 'snow',
                'rally_img' => 'rally_images/rally-aluksne-2025.png',
            ],
            [
                'rally_name' => 'Rallijs Sarma',
                'rally_tag' => 'rally-sarma',
                'date_from' => '2025-02-07',
                'date_to' => '2025-02-08',
                'location' => 'lv',
                'road_surface' => 'snow',
                'rally_img' => 'rally_images/rally-sarma-2025.png',
            ],
            [
                'rally_name' => 'Rallijsprints Vidzeme',
                'rally_tag' => 'rally-sprint-vidzeme',
                'date_from' => '2025-04-26',
                'date_to' => '2025-04-27',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/rally-sprint-vidzeme-2025.png',
            ],
            [
                'rally_name' => 'Rallijsprints Talsi',
                'rally_tag' => 'rally-sprint-talsi',
                'date_from' => '2025-06-28',
                'date_to' => '2025-06-29',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/rally-sprint-talsi-2025.png',
            ],
            [
                'rally_name' => 'Rally LÕUNA-EESTI',
                'rally_tag' => 'rally-louna-eesti',
                'date_from' => '2025-07-11',
                'date_to' => '2025-07-12',
                'location' => 'ee',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/rally-louna-eesti-2025.png',
            ],
            [
                'rally_name' => 'Rallijs Paide',
                'rally_tag' => 'rally-paide',
                'date_from' => '2025-08-22',
                'date_to' => '2025-08-23',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/rally-paide-2025.png',
            ],
            [
                'rally_name' => 'Rallijs Cēsis',
                'rally_tag' => 'rally-cesis',
                'date_from' => '2025-09-19',
                'date_to' => '2025-09-20',
                'location' => 'lv',
                'road_surface' => 'gravel',
                'rally_img' => 'rally_images/rally-cesis-2025.png',
            ],
            [
                'rally_name' => 'Rallijsprints Latvija',
                'rally_tag' => 'rally-sprint-latvia',
                'date_from' => '2025-10-25',
                'date_to' => '2025-10-26',
                'location' => 'lv',
                'road_surface' => 'tarmac',
                'rally_img' => 'rally_images/rally-sprint-latvia-2025.png',
            ],
        ];

        foreach ($rallies2025 as $rallyData) {
            Rally::create(array_merge($rallyData, ['season_id' => $season2025->id]));
        }
    }
}
