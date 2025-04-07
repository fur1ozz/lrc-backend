<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SponsorsSeeder extends Seeder
{
    public function run(): void
    {
        $sponsors = [
            [
                'name' => 'Pirelli',
                'file' => 'Pirelli_logo.png',
                'url' => 'https://www.pirelli.com/',
            ],
            [
                'name' => 'Neste',
                'file' => 'Neste_logo.png',
                'url' => 'https://www.neste.lv/',
            ],
            [
                'name' => 'LMT',
                'file' => 'LMT.png',
                'url' => 'https://www.lmt.lv/',
            ],
            [
                'name' => 'Bosch',
                'file' => 'Bosch-logo.png',
                'url' => 'https://www.bosch.com/',
            ],
            [
                'name' => 'TV3 Group',
                'file' => 'TV3.png',
                'url' => 'https://tv3.lv/',
            ],
            [
                'name' => 'Shell Helix',
                'file' => 'Shell_Helix3.png',
                'url' => 'https://www.shell.com/motorist/oils-lubricants/helix.html',
            ],
            [
                'name' => 'Hankook',
                'file' => 'Hankook_Logo.png',
                'url' => 'https://www.hankooktire.com/global/en/home.html',
            ],
            [
                'name' => 'Bosch Car Service',
                'file' => 'bosch-service.png',
                'url' => 'https://www.boschcarservice.com/',
            ],
            [
                'name' => 'Castrol',
                'file' => 'Castrol_logo.png',
                'url' => 'https://www.castrol.com/',
            ],
            [
                'name' => 'Mobil 1',
                'file' => 'Mobil1_logo.png',
                'url' => 'https://www.mobil.com/',
            ],
            [
                'name' => 'TotalEnergies',
                'file' => 'TotalEnergies_logo.png',
                'url' => 'https://totalenergies.com/',
            ],
            [
                'name' => 'Red Bull',
                'file' => 'RedBullEnergyDrink.png',
                'url' => 'https://www.redbull.com/',
            ],
            [
                'name' => 'Michelin',
                'file' => 'Michelin.png',
                'url' => 'https://www.michelin.com/',
            ],
            [
                'name' => 'GoPro',
                'file' => 'GoPro_logo.png',
                'url' => 'https://www.gopro.com/',
            ],
            [
                'name' => 'Sparco',
                'file' => 'Sparco.png',
                'url' => 'https://www.sparco-official.com/',
            ],
            [
                'name' => 'Liqui Moly',
                'file' => 'Liqui-moly.png',
                'url' => 'https://www.liqui-moly.com/',
            ],
            [
                'name' => 'Garmin',
                'file' => 'Garmin.png',
                'url' => 'https://www.garmin.com/',
            ],
            [
                'name' => 'NGK Spark Plugs',
                'file' => 'NGK-Logo.png',
                'url' => 'https://www.ngkntk.com/',
            ],
            [
                'name' => 'Alpinestars',
                'file' => 'Alpinestars_logo.png',
                'url' => 'https://www.alpinestars.com/',
            ],
        ];

        // Ensure the sponsors folder exists
        if (!Storage::disk('public')->exists('sponsors')) {
            Storage::disk('public')->makeDirectory('sponsors');
        }

        foreach ($sponsors as $sponsor) {
            $sourcePath = database_path("seeders/images/sponsors/{$sponsor['file']}");
            $targetPath = "sponsors/{$sponsor['file']}";

            if (file_exists($sourcePath) && !Storage::disk('public')->exists($targetPath)) {
                Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
            }

            Sponsor::create([
                'name' => $sponsor['name'],
                'image' => $targetPath,
                'url' => $sponsor['url'],
            ]);
        }
    }
}
