<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Rally;

class GalleryImagesSeeder extends Seeder
{
    public function run()
    {
        if (!Storage::disk('public')->exists('gallery')) {
            Storage::disk('public')->makeDirectory('gallery');
        }

        $imageNames = [
            'img1.jpg',
            'img2.jpg',
            'img3.jpg',
            'img4.jpg',
            'img5.jpg',
            'img6.jpg',
        ];

        foreach ($imageNames as $image) {
            $sourcePath = database_path("seeders/images/gallery_images/{$image}");
            $targetPath = "gallery/{$image}";

            if (file_exists($sourcePath) && !Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
            }
        }

        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            foreach ($imageNames as $image) {
                GalleryImage::create([
                    'rally_id' => $rally->id,
                    'img_src' => "gallery/{$image}",
                ]);
            }
        }
    }
}
