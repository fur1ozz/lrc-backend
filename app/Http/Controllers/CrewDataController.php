<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Team;
use App\Models\Crew;
use App\Models\Group;
use App\Models\CrewGroupInvolvement;
use Illuminate\Support\Facades\Storage;  // For reading the JSON file

class CrewDataController extends Controller
{
    // Method to handle the import via API request
    public function importCrewData($rallyId)
    {
        // Read the JSON file from the storage folder
        $json = Storage::get('crew_data.json');  // Ensure you have crew_data.json in storage/app
        $data = json_decode($json, true);

        // Check if JSON data is valid
        if (!$data) {
            return response()->json(['error' => 'Invalid JSON data'], 400);
        }

        // Call the function to insert data
        $this->insertCrewData($data, $rallyId);

        return response()->json(['message' => 'Crew data imported successfully.']);
    }

    // The function that handles data insertion
    protected function insertCrewData(array $data, $rallyId)
    {
        foreach ($data as $entry) {
            // Process driver name
            $driverNameParts = explode(' ', $entry['driver']);
            $driverName = $this->handleNameParts($driverNameParts);

            // Process co-driver name
            $coDriverNameParts = explode(' ', $entry['coDriver']);
            $coDriverName = $this->handleNameParts($coDriverNameParts);

            // Insert Driver
            $driver = Participant::create([
                'name' => $driverName['first_name'],
                'surname' => $driverName['surname'],
                'desc' => '',
                'nationality' => $entry['nationality'],
                'image' => ''
            ]);

            // Insert Co-driver
            $coDriver = Participant::create([
                'name' => $coDriverName['first_name'],
                'surname' => $coDriverName['surname'],
                'desc' => '',
                'nationality' => $entry['nationality'],
                'image' => ''
            ]);

            // Insert Team
            $team = Team::create([
                'team_name' => $entry['team'],
                'manager_name' => 'Some Manager',  // Add placeholder or actual data
                'manager_contact' => 'manager@example.com'  // Placeholder contact
            ]);

            // Insert Crew
            $crew = Crew::create([
                'driver_id' => $driver->id,
                'co_driver_id' => $coDriver->id,
                'team_id' => $team->id,
                'rally_id' => $rallyId,  // Example rally_id, adjust as needed
                'crew_number' => $entry['number'],
                'car' => $entry['car'],
                'drive_type' => $entry['group'],
                'drive_class' => $entry['class']
            ]);

            // Find and associate Group
            $group = Group::where('group_name', $entry['eligibility'])->first();

            if ($group) {
                CrewGroupInvolvement::create([
                    'crew_id' => $crew->id,
                    'group_id' => $group->id,
                    'rally_id' => $rallyId // Example rally_id, adjust as needed
                ]);
            }
        }
    }

    // Function to handle name splitting and assignment
    protected function handleNameParts(array $nameParts)
    {
        $nameCount = count($nameParts);

        if ($nameCount === 1) {
            // Only one part, consider it as the first name
            return [
                'first_name' => $nameParts[0],
                'surname' => ''
            ];
        } elseif ($nameCount === 2) {
            // Two parts, standard first name and surname
            return [
                'first_name' => $nameParts[0],
                'surname' => $nameParts[1]
            ];
        } elseif ($nameCount >= 3) {
            // Three or more parts, treat the first two as the first name, and the rest as surname
            $firstName = $nameParts[0] . ' ' . $nameParts[1];
            $surname = implode(' ', array_slice($nameParts, 2));
            return [
                'first_name' => $firstName,
                'surname' => $surname
            ];
        }

        return [
            'first_name' => '',
            'surname' => ''
        ]; // Default fallback
    }
}
