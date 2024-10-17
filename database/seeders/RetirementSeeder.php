<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Participant;
use App\Models\Retirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RetirementSeeder extends Seeder
{
    public function run()
    {
        $json = Storage::get('retirement_data.json');
        $retirementData = json_decode($json, true);

        $rallyId = 4;

        foreach ($retirementData as $data) {
            list($driverFirstName, $driverLastName) = explode(' ', $data['driver'], 2);
            list($coDriverFirstName, $coDriverLastName) = explode(' ', $data['coDriver'], 2);

            $drivers = Participant::where('name', $driverFirstName)
                ->where('surname', $driverLastName)
                ->get();

            $coDrivers = Participant::where('name', $coDriverFirstName)
                ->where('surname', $coDriverLastName)
                ->get();

            if ($drivers->isEmpty() || $coDrivers->isEmpty()) {
                $this->command->info("Driver or Co-driver not found: {$data['driver']} / {$data['coDriver']}");
                continue;
            }

            foreach ($drivers as $driver) {
                foreach ($coDrivers as $coDriver) {
                    $crew = Crew::where('rally_id', $rallyId)
                        ->where('driver_id', $driver->id)
                        ->where('co_driver_id', $coDriver->id)
                        ->first();

                    if ($crew) {
                        try {
                            Retirement::create([
                                'crew_id' => $crew->id,
                                'rally_id' => $rallyId,
                                'retirement_reason' => $data['retireReason'],
                                'stage_of_retirement' => (int)$data['finishedStages'] + 1,
                            ]);

                            $this->command->info("Inserted retirement for crew {$data['crewNumber']} - {$data['retireReason']}");
                        } catch (\Exception $e) {
                            $this->command->error("Error inserting retirement for crew {$data['crewNumber']}: {$e->getMessage()}");
                        }
                    } else {
                        $this->command->error("Crew not found: Crew {$data['crewNumber']} - {$data['driver']} / {$data['coDriver']}");
                    }
                }
            }
        }
    }
}
