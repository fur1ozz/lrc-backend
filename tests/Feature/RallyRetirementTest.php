<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\Participant;
use App\Models\Rally;
use App\Models\Retirement;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RallyRetirementTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_error_message_when_rally_not_found()
    {
        $response = $this->getJson('/api/rally-retirements/2000/rally-not-existing');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Rally not found for this season',
            ]);
        $this->assertTrue(true, 'Tested rally not found case successfully');

    }
    public function test_returns_correct_json_structure_with_empty_array_when_no_data_available()
    {
        $year = 2000;
        $season = Season::create(['year' => $year]);

        $rally = Rally::create([
            'rally_name'=>'Rally De Existingo',
            'date_from'=>$year.'-01-10',
            'date_to'=>$year.'-01-11',
            'location'=>'existing place',
            'road_surface'=>'gravel',
            'rally_tag'=>'rally-existing',
            'season_id'=>'1',
            'rally_sequence'=>'1',
        ]);

        $response = $this->getJson('/api/rally-retirements/'.$year.'/rally-existing');

        $response->assertStatus(200)
            ->assertJson([]);
    }
    public function test_returns_correct_json_structure_with_data_about_rally_retirements()
    {
        $year = 2000;
        $season = Season::create(['year' => $year]);

        $rally = Rally::create([
            'rally_name'=>'Rally De Existingo',
            'date_from'=>$year.'-01-10',
            'date_to'=>$year.'-01-11',
            'location'=>'existing place',
            'road_surface'=>'gravel',
            'rally_tag'=>'rally-existing',
            'season_id'=>$season->id,
            'rally_sequence'=>'1',
        ]);

        $participant = Participant::create([
            'name'=>'John',
            'surname'=>'Doe',
            'desc'=>'',
            'nationality'=>'LV',
            'image'=>null,
        ]);
        $participant2 = Participant::create([
            'name'=>'Peter',
            'surname'=>'Smith',
            'desc'=>'',
            'nationality'=>'LV',
            'image'=>null,
        ]);

        $team = Team::create([
            'team_name'=>'Beasts',
            'manager_name'=>'John Pork',
            'manager_contact'=>'none',
        ]);

        $crew = Crew::create([
            'driver_id'=>$participant->id,
            'co_driver_id'=>$participant2->id,
            'team_id'=>$team->id,
            'rally_id'=>$rally->id,
            'crew_number'=>1,
            'car'=>'opel corsa',
            'drive_type'=>'2wd',
            'drive_class'=>'retro',
        ]);

        $retirements = Retirement::create([
            'crew_id'=>$crew->id,
            'rally_id'=>$rally->id,
            'retirement_reason'=>'Engine',
            'stage_of_retirement'=>3,
        ]);

        $response = $this->getJson('/api/rally-retirements/'.$year.'/'.$rally->rally_tag);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    "crew_id",
                    "crew_number",
                    "car",
                    "drive_type",
                    "driver" => [
                        "id",
                        "name",
                        "surname",
                        "nationality"
                    ],
                    "co_driver" => [
                        "id",
                        "name",
                        "surname",
                        "nationality"
                    ],
                    "retirement" => [
                        "retirement_reason",
                        "stage_of_retirement",
                        "finished_stages",
                    ],
                ]
            ]);

    }


}
