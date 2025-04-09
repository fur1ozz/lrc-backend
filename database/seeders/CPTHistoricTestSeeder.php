<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CPTHistoricTestSeeder extends Seeder
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


        $driveTypeMapping = [
            'AWD' => [23],
            '2WD' => [24]
        ];

        $rallies = DB::table('rallies')->pluck('id');

        $drivers = [];
        $coDrivers = [];

        for ($i = 0; $i < 20; $i++) {
            $drivers[] = DB::table('participants')->insertGetId([
                'name' => $faker->firstName,
                'surname' => $faker->lastName,
                'nationality' => (rand(1, 10) <= 6) ? 'lv' : $faker->randomElement($preferredCountries),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $coDrivers[] = DB::table('participants')->insertGetId([
                'name' => $faker->firstName,
                'surname' => $faker->lastName,
                'nationality' => (rand(1, 10) <= 6) ? 'lv' : $faker->randomElement($preferredCountries),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $driverMemory = [];
        $coDriverMemory = [];
        $teamMemory = [];

        foreach ($rallies as $rallyId) {
            $historicClasses = DB::table('rally_classes')
                ->where('rally_id', $rallyId)
                ->whereIn('class_id', [23, 24])
                ->pluck('class_id');

            $crewNumber = 1;
            shuffle($drivers);

            $numCrews = rand(10, 20);

            foreach (array_slice($drivers, 0, $numCrews) as $driverId) {
                $classId = null;
                $className = null;
                $driveType = null;
                $car = null;


                if (isset($driverMemory[$driverId])) {
                    $classId = $driverMemory[$driverId]['class_id'];
                    $className = DB::table('group_classes')->where('id', $classId)->value('class_name');
                    $driveType = $driverMemory[$driverId]['drive_type'];
                    $car = $driverMemory[$driverId]['car'];
                } else {
                    $classId = $historicClasses->random();
                    $className = DB::table('group_classes')->where('id', $classId)->value('class_name');

                    foreach ($driveTypeMapping as $type => $ids) {
                        if (in_array($classId, $ids)) {
                            $driveType = $type;
                            break;
                        }
                    }

                    if (!$driveType) {
                        continue;
                    }

                    if ($driveType === '2WD') {
                        $randomDriveType = $faker->randomElement(['FWD', 'RWD']);
                        $car = $faker->randomElement($historic_cars[$randomDriveType]);
                        $driveType = $randomDriveType;
                    } else {
                        $car = $faker->randomElement($historic_cars[$driveType]);
                    }

                    $driverMemory[$driverId] = [
                        'class_id' => $classId,
                        'class_name' => $className,
                        'car' => $car,
                        'drive_type' => $driveType
                    ];
                }

                $groupId = DB::table('group_classes')->where('id', $classId)->value('group_id');

                // Co-driver (80% reuse)
                if (isset($coDriverMemory[$driverId]) && rand(1, 10) <= 8) {
                    $coDriverId = $coDriverMemory[$driverId];
                } else {
                    $coDriverId = $faker->randomElement($coDrivers);
                    $coDriverMemory[$driverId] = $coDriverId;
                }

                if (isset($teamMemory[$driverId])) {
                    $teamId = $teamMemory[$driverId];
                } else {
                    $teamId = DB::table('teams')->insertGetId([
                        'team_name' => $faker->company,
                        'manager_name' => $faker->name,
                        'manager_contact' => $faker->email,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $teamMemory[$driverId] = $teamId;
                }

                $crewId = DB::table('crews')->insertGetId([
                    'driver_id' => $driverId,
                    'co_driver_id' => $coDriverId,
                    'team_id' => $teamId,
                    'rally_id' => $rallyId,
                    'crew_number_int' => $crewNumber,
                    'is_historic' => true,
                    'car' => $car,
                    'drive_type' => $driveType,
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
