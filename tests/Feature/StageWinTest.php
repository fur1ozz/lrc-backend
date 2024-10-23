<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\Participant;
use App\Models\Rally;
use App\Models\Season;
use App\Models\Stage;
use App\Models\StageResults;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StageWinTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_error_message_when_rally_not_found()
    {
        $response = $this->getJson('/api/rally-winner-results/2000/rally-not-existing');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Rally not found for this season',
            ]);
        $this->assertTrue(true, 'Tested rally not found case successfully');

    }

    public function test_returns_correct_json_structure_with_empty_stage_results_and_top3_results_when_no_data_available()
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

        $response = $this->getJson('/api/rally-winner-results/'.$year.'/rally-existing');

        $response->assertStatus(200)
            ->assertJson([
                'winner_results' => [
                    'stages' => [],
                    'top_3_result' => [],
                ],
            ]);
    }

    public function test_returns_correct_json_structure_with_data_about_stage_results_and_top3_results()
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

        $stage = Stage::create([
            'rally_id'=>$rally->id,
            'stage_name'=>'JOHN PORK',
            'stage_number'=>1,
            'distance_km'=>23.44,
            'start_date'=>$year.'-10-15',
            'start_time'=>'21:44:04',
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

        $stageResult = StageResults::create([
            'crew_id'=>$crew->id,
            'stage_id'=>$stage->id,
            'crew_start_time'=>null,
            'time_taken'=>'06:35.47',
            'avg_speed'=>'104.23'
        ]);

        $response = $this->getJson('/api/rally-winner-results/'.$year.'/'.$rally->rally_tag);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'winner_results' => [
                    'stages' => [
                        '*' => [
                            'stage_number',
                            'stage_name',
                            'stage_distance',
                            'stage_winner' => [
                                'place',
                                'crew_number',
                                'driver',
                                'driver_nationality',
                                'co_driver',
                                'co_driver_nationality',
                                'team',
                                'vehicle',
                                'drive_type',
                                'completion_time',
                                'average_speed_kmh',
                            ],
                        ],
                    ],
                    'top_3_result' => [
                        '*' => [
                            'crew_id',
                            'crew_number',
                            'driver',
                            'driver_nationality',
                            'co_driver',
                            'co_driver_nationality',
                            'team',
                            'vehicle',
                            'drive_type',
                            'total_stage_wins',
                            'total_second_places',
                            'total_third_places',
                        ],
                    ],
                ],
            ]);

    }
}
