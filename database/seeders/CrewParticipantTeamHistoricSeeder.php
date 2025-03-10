<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CrewParticipantTeamHistoricSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $preferredCountries = [
            'lv', // Latvia
            'ee', // Estonia
            'lt', // Lithuania
            'es', // Spain
            'gb', // UK
            'fi', // Finland
            'se', // Sweden
            'pl', // Poland
            'tr', // Turkey
            'gr', // Greece
            'be', // Belgium
        ];

        $historic_cars = [
            'AWD' => [
                'Audi Quattro S1', 'Peugeot 205 T16', 'Lancia Delta S4', 'Ford RS200',
                'MG Metro 6R4',
            ],
            'RWD' => [
                'Lancia Stratos HF', 'Porsche 911 Carrera RS', 'Ford Escort RS1800', 'Fiat 131 Abarth',
                'Opel Ascona 400', 'Alpine A110', 'Renault 17 Gordini', 'Datsun 240Z',
                'Volvo 142 Rally', 'Saab 96 V4',
            ],
            'FWD' => [
                'Citroën Saxo Kit Car', 'Suzuki Ignis S1600', 'Daihatsu Charade GTti Rally', 'Fiat Punto S1600',
            ],
        ];


        // Mapping class IDs to drive types
        $driveTypeMapping = [
            'AWD' => [23],
            '2WD' => [24]
        ];

        $rallies = DB::table('rallies')->pluck('id');

        foreach ($rallies as $rallyId) {
            // Get only historic classes that are allowed by the rally
            $classes = DB::table('rally_classes')
                ->where('rally_id', $rallyId)
                ->whereIn('class_id', [23, 24, 25])
                ->pluck('class_id');

            $crewNumber = 1;
            foreach ($classes as $classId) {
                $className = DB::table('group_classes')->where('id', $classId)->value('class_name');
                $groupId = DB::table('group_classes')->where('id', $classId)->value('group_id');

                $driveType = null;
                foreach ($driveTypeMapping as $type => $ids) {
                    if (in_array($classId, $ids)) {
                        $driveType = $type;
                        break;
                    }
                }

                if (!$driveType) {
                    continue;
                }

                for ($i = 0; $i < 5; $i++) {
                    // 60% chance for Latvian driver nationality
                    $driverNationality = (rand(1, 10) <= 6) ? 'lv' : $faker->randomElement($preferredCountries);

                    $driverId = DB::table('participants')->insertGetId([
                        'name' => $faker->firstName,
                        'surname' => $faker->lastName,
                        'nationality' => $driverNationality,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Decide whether to make co-driver nationality different (10% chance)
                    $coDriverNationality = $driverNationality;
                    if (rand(1, 10) <= 1) {
                        $coDriverNationality = $faker->randomElement($preferredCountries);
                    }

                    $coDriverId = DB::table('participants')->insertGetId([
                        'name' => $faker->firstName,
                        'surname' => $faker->lastName,
                        'nationality' => $coDriverNationality,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Pick a random historic car based on the drive type
                    if ($driveType === '2WD') {
                        $randomDriveType = $faker->randomElement(['FWD', 'RWD']);
                        $car = $faker->randomElement($historic_cars[$randomDriveType]);
                        $finalDriveType = $randomDriveType;
                    } else {
                        $car = $faker->randomElement($historic_cars[$driveType]);
                        $finalDriveType = $driveType;
                    }

                    $teamId = DB::table('teams')->insertGetId([
                        'team_name' => $faker->company,
                        'manager_name' => $faker->name,
                        'manager_contact' => $faker->email,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $crewId = DB::table('crews')->insertGetId([
                        'driver_id' => $driverId,
                        'co_driver_id' => $coDriverId,
                        'team_id' => $teamId,
                        'rally_id' => $rallyId,
                        'crew_number' => 'H'.$crewNumber,
                        'car' => $car,
                        'drive_type' => $finalDriveType,
                        'drive_class' => $className,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('crew_group_involvements')->insert([
                        'crew_id' => $crewId,
                        'group_id' => $groupId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('crew_class_involvements')->insert([
                        'crew_id' => $crewId,
                        'class_id' => $classId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $crewNumber++;
                }

            }
        }
    }
}
