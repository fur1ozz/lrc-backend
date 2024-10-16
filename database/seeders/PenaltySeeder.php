<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Crew;
use App\Models\Stage;
use App\Models\Penalties;
use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PenaltySeeder extends Seeder
{
    public function run()
    {
        $json = Storage::get('penalty_data.json');  // Ensure you have crew_data.json in storage/app
        $penaltiesData = json_decode($json, true);

        $rallyId = 4;  // We're working with rally ID 4

        foreach ($penaltiesData as $data) {
            // Split driver and co-driver full names
            list($driverFirstName, $driverLastName) = explode(' ', $data['driver'], 2);
            list($coDriverFirstName, $coDriverLastName) = explode(' ', $data['coDriver'], 2);

            // Retrieve all participants to avoid ambiguity
            $drivers = Participant::where('name', $driverFirstName)
                ->where('surname', $driverLastName)
                ->get();

            $coDrivers = Participant::where('name', $coDriverFirstName)
                ->where('surname', $coDriverLastName)
                ->get();

            // Log to check if participants were found
            if ($drivers->isEmpty() || $coDrivers->isEmpty()) {
                $this->command->info("Driver or Co-driver not found: {$data['driver']} / {$data['coDriver']}");
                continue; // Skip this crew if we can't find both participants
            }

            // Check each driver and co-driver against the crew for the specific rally
            foreach ($drivers as $driver) {
                foreach ($coDrivers as $coDriver) {
                    $crew = Crew::where('rally_id', $rallyId)
                        ->where('driver_id', $driver->id)
                        ->where('co_driver_id', $coDriver->id)
                        ->first();

                    if ($crew) {
                        $this->command->info("Found crew: {$data['crewNumber']} - {$data['driver']} / {$data['coDriver']}");

                        foreach ($data['penalties'] as $penalty) {
                            // Extract stage number from the reason (e.g., SS-1 or TC-1)
                            if (preg_match('/[STC]-([0-9]+)/', $penalty['reason'], $matches)) {
                                $stageNumber = $matches[1];

                                $penaltyTimeParts = explode(':', $penalty['penaltyTime']);
                                $penaltyTime = sprintf('%02d:%02d:%02d', 0, (int)$penaltyTimeParts[0], (int)round($penaltyTimeParts[1])); // Ensuring correct time format


                                // Find the stage ID based on rally_id and stage_number
                                $stage = Stage::where('rally_id', $rallyId)
                                    ->where('stage_number', $stageNumber)
                                    ->first();

                                if ($stage) {
                                    // Insert the penalty record into the database
                                    try {
                                        Penalties::create([
                                            'crew_id' => $crew->id,
                                            'stage_id' => $stage->id,
                                            'penalty_type' => $penalty['reason'],
                                            'penalty_amount' => $penaltyTime,
                                        ]);

                                        $this->command->info("Inserted penalty for crew {$data['crewNumber']} at stage {$stageNumber}: {$penalty['reason']}");
                                    } catch (\Exception $e) {
                                        $this->command->error("Error inserting penalty for crew {$data['crewNumber']} at stage {$stageNumber}: {$e->getMessage()}");
                                    }
                                } else {
                                    $this->command->error("Stage not found: Stage {$stageNumber} for rally ID {$rallyId}");
                                }
                            } else {
                                $this->command->error("Invalid stage number format in reason: {$penalty['reason']}");
                            }
                        }
                    } else {
                        $this->command->error("Crew not found: Crew {$data['crewNumber']} - {$data['driver']} / {$data['coDriver']}");
                    }
                }
            }
        }
    }
}
