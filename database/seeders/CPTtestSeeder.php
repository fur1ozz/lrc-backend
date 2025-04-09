<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CPTtestSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('crews')->truncate();
        DB::table('participants')->truncate();
        DB::table('teams')->truncate();
        DB::table('crew_group_involvements')->truncate();
        DB::table('crew_class_involvements')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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

        $cars = [
            'AWD' => [
                'Subaru Impreza', 'Ford Fiesta R5', 'Citroen C3 WRC', 'Toyota Yaris WRC',
                'Mitsubishi Lancer Evo X', 'Hyundai i20 WRC', 'Skoda Fabia R5', 'Volkswagen Polo GTI R5',
                'Audi Quattro S1', 'Toyota Celica GT-Four ST205', 'Lancia Delta Integrale',
                'Peugeot 205 T16', 'MG Metro 6R4', 'Mini Cooper JCW WRC', 'Mitsubishi Mirage R5', 'Subaru Legacy RS'
            ],
            'FWD' => [
                'Peugeot 208 Rally4', 'Opel Corsa Rally4', 'Renault Clio Rally5', 'Honda Civic Type R Rally',
                'Suzuki Swift Sport Rally', 'Dacia Sandero Rally Cup', 'Volkswagen Golf Kit Car',
                'Peugeot 106 Maxi', 'Citroën Saxo Kit Car', 'Suzuki Ignis S1600', 'Daihatsu Charade GTti Rally',
                'Fiat Punto S1600'
            ],
            'RWD' => [
                'Ford Escort MK2', 'Lada 2105', 'Porsche 911 GT3 Rally', 'Fiat Abarth 124 Rally',
                'BMW M3 E30 Rally', 'Mazda RX-7 Group B', 'Opel Manta 400', 'Fiat 131 Abarth',
                'Toyota Starlet KP61', 'Nissan 240RS', 'Datsun 160J', 'Škoda 130 RS',
                'Ford Sierra RS Cosworth', 'Renault 5 Turbo', 'Alpine A110 Rally', 'Lancia Stratos HF',
                'Volvo 242 Turbo Rally', 'Chevrolet Corvette Rally', 'Ferrari 308 GTB Rally', 'Lotus Sunbeam Talbot'
            ],
        ];

        // Mapping class IDs to drive types
        $driveTypeMapping = [
            'AWD' => [1, 2, 3, 5, 9, 10, 11, 13, 20],
            'FWD' => [4, 7, 8, 12, 15, 19],
            'RWD' => [6, 14],
            '2WD' => [21, 24]
        ];

        $rallies = DB::table('rallies')->pluck('id');

        $drivers = [];
        $coDrivers = [];

        for ($i = 0; $i < 200; $i++) {
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

        // Stores info about drivers car, class and drive type, to later determine correct values
        $driverMemory = [];
        // Stores info about drivers previous co-drivers, to later determine correct values
        $coDriverMemory = [];
        $teamMemory = [];

        foreach ($rallies as $rallyId) {
            $classes = DB::table('rally_classes')
                ->where('rally_id', $rallyId)
                // 16, 17, 18, 22, 26, 27, 28, 29, 30, 31 aren't used currently
                ->whereNotIn('class_id', [23, 24, 25, 16, 17, 18, 22, 26, 27, 28, 29, 30, 31])
                ->pluck('class_id');

            $crewNumber = 1;
            shuffle($drivers);

            $numCrews = rand(70, 90);

            foreach (array_slice($drivers, 0, $numCrews) as $driverId) {
                $classId = null;
                $className = null;
                $driveType = null;
                $car = null;

                // 80% chance to reuse previous class/car
                if (isset($driverMemory[$driverId]) && rand(1, 10) <= 8) {
                    $classId = $driverMemory[$driverId]['class_id'];
                    $className = DB::table('group_classes')->where('id', $classId)->value('class_name');
                    $driveType = $driverMemory[$driverId]['drive_type'];
                    $car = $driverMemory[$driverId]['car'];
                } else {
                    $classId = $classes->random();
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
                        $car = $faker->randomElement($cars[$randomDriveType]);
                        $driveType = $randomDriveType;
                    } else {
                        $car = $faker->randomElement($cars[$driveType]);
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

                // Team (80% reuse)
                if (isset($teamMemory[$driverId]) && rand(1, 10) <= 8) {
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

                // Crew creation
                $crewId = DB::table('crews')->insertGetId([
                    'driver_id' => $driverId,
                    'co_driver_id' => $coDriverId,
                    'team_id' => $teamId,
                    'rally_id' => $rallyId,
                    'crew_number_int' => $crewNumber,
                    'is_historic' => false,
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
