<?php

namespace Database\Seeders;

use App\Models\Rally;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RallySponsorsSeeder extends Seeder
{
    public function run(): void
    {
        $typeMap = [
            'Pirelli' => 'Tire Sponsor',
            'Hankook' => 'Tire Sponsor',
            'Michelin' => 'Tire Sponsor',

            'Mobil 1' => 'Oil Partner',
            'Castrol' => 'Oil Partner',
            'Liqui Moly' => 'Lubricant Partner',
            'TotalEnergies' => 'Energy Partner',

            'Red Bull' => 'Official Beverage',
            'Neste' => 'Main Sponsor',
            'LMT' => 'Supporter',
            'TV3 Group' => 'Media Partner',
            'Shell Helix' => 'Supporter',

            'Bosch' => 'In Collaboration With',
            'Bosch Car Service' => 'In Collaboration With',

            'GoPro' => 'Tech Partner',
            'Garmin' => 'Navigation Partner',
            'NGK Spark Plugs' => 'Ignition Sponsor',
            'Alpinestars' => 'Apparel Partner',
            'Sparco' => 'Racing Gear Sponsor',
        ];

        $rallies = Rally::all();
        $sponsors = Sponsor::all();

        $insertData = [];

        foreach ($rallies as $rally) {
            foreach ($sponsors as $sponsor) {
                $insertData[] = [
                    'rally_id' => $rally->id,
                    'sponsor_id' => $sponsor->id,
                    'type' => $typeMap[$sponsor->name] ?? 'General Sponsor',
                ];
            }
        }

        DB::table('rally_sponsor')->insert($insertData);
    }
}
