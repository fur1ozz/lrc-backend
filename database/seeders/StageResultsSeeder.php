<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Participant;
use App\Models\Stage;
use App\Models\StageResults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StageResultsSeeder extends Seeder
{
    public function run()
    {
        // Load the JSON file
        $json = Storage::get('overall_and_stage_results.json');
        $stageResultsData = json_decode($json, true);

        // Set rally_id to 4 as requested
        $rallyId = 4;

        // Loop through each entry in the JSON data
        foreach ($stageResultsData as $data) {
            // Extract driver and co-driver names
            list($driverFirstName, $driverLastName) = explode(' ', $data['driver'], 2);
            list($coDriverFirstName, $coDriverLastName) = explode(' ', $data['co_driver'], 2);

            // Find driver and co-driver participants
            $drivers = Participant::where('name', $driverFirstName)
                ->where('surname', $driverLastName)
                ->get();

            $coDrivers = Participant::where('name', $coDriverFirstName)
                ->where('surname', $coDriverLastName)
                ->get();

            // If no matching drivers or co-drivers are found, log a message and skip
            if ($drivers->isEmpty() || $coDrivers->isEmpty()) {
                $this->command->info("Driver or Co-driver not found: {$data['driver']} / {$data['co_driver']}");
                continue;
            }

            // Loop through all possible driver/co-driver combinations to find the crew
            foreach ($drivers as $driver) {
                foreach ($coDrivers as $coDriver) {
                    $crew = Crew::where('rally_id', $rallyId)
                        ->where('driver_id', $driver->id)
                        ->where('co_driver_id', $coDriver->id)
                        ->first();

                    // If the crew exists, process the stage results
                    if ($crew) {
                        $this->command->info("Found crew: {$data['crew_number']} - {$data['driver']} / {$data['co_driver']}");

                        // Loop through each stage time in the data
                        foreach ($data['stage_times'] as $stageTime) {
                            $stageNumber = $stageTime['stage_number'];

                            // Find the corresponding stage by rally ID and stage number
                            $stage = Stage::where('rally_id', $rallyId)
                                ->where('stage_number', $stageNumber)
                                ->first();

                            // If stage not found, log and continue
                            if (!$stage) {
                                $this->command->info("Stage not found: Stage {$stageNumber} for rally {$rallyId}");
                                continue;
                            }

                            // Convert stage time to seconds for calculation
                            $timeInSeconds = $this->convertTimeToSeconds($stageTime['time']);

                            // Convert the time back to MM:SS.mm format for storage
                            $formattedTime = $this->convertSecondsToFormattedTime($timeInSeconds);

                            // Calculate average speed (distance in km / time in hours)
                            $distanceKm = $stage->distance_km;
                            $timeInHours = $timeInSeconds / 3600;
                            $avgSpeed = $distanceKm / $timeInHours;

                            // Insert the stage result into the stage_results table
                            try {
                                StageResults::create([
                                    'crew_id' => $crew->id,
                                    'stage_id' => $stage->id,
                                    'crew_start_time' => null, // Assuming no start time provided
                                    'time_taken' => $formattedTime, // Use formatted time
                                    'avg_speed' => round($avgSpeed, 2), // Round to 2 decimal places
                                ]);

                                $this->command->info("Inserted stage result for crew {$data['crew_number']} on stage {$stageNumber} with avg speed: {$avgSpeed}");
                            } catch (\Exception $e) {
                                $this->command->error("Error inserting stage result for crew {$data['crew_number']} on stage {$stageNumber}: {$e->getMessage()}");
                            }
                        }
                    } else {
                        $this->command->error("Crew not found: Crew {$data['crew_number']} - {$data['driver']} / {$data['co_driver']}");
                    }
                }
            }
        }
    }

    // Function to convert time from mm:ss.sss or hh:mm:ss format to seconds
    private function convertTimeToSeconds($time)
    {
        $timeParts = explode(':', $time);
        $totalSeconds = 0;

        if (count($timeParts) == 3) {
            // Format hh:mm:ss
            $totalSeconds += intval($timeParts[0]) * 3600; // Hours to seconds
            $totalSeconds += intval($timeParts[1]) * 60;   // Minutes to seconds
            $totalSeconds += floatval($timeParts[2]);      // Seconds
        } elseif (count($timeParts) == 2) {
            // Format mm:ss or mm:ss.sss
            $totalSeconds += intval($timeParts[0]) * 60;   // Minutes to seconds

            // Handle seconds with milliseconds
            if (strpos($timeParts[1], '.') !== false) {
                $subParts = explode('.', $timeParts[1]);
                $totalSeconds += intval($subParts[0]); // Seconds
                $totalSeconds += isset($subParts[1]) ? floatval($subParts[1]) / 1000 : 0; // Milliseconds to seconds
            } else {
                $totalSeconds += floatval($timeParts[1]); // Just seconds
            }
        }

        return $totalSeconds;
    }

    // Function to convert seconds to MM:SS.mm format
    private function convertSecondsToFormattedTime($seconds)
    {
        $milliseconds = ($seconds - floor($seconds)) * 1000; // Get milliseconds
        $seconds = floor($seconds);
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        // Return formatted time
        return sprintf('%02d:%02d.%02d', $minutes, $remainingSeconds, $milliseconds);
    }
}
