<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\Participant;
use App\Models\Penalties;
use App\Models\Rally;
use App\Models\Season;
use App\Models\Stage;
use App\Models\StageResults;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RallyOverallResultsCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_error_message_when_rally_not_found()
    {
        $response = $this->getJson('/api/calculate/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Rally not found',
            ]);
        $this->assertTrue(true, 'Tested rally not found case successfully');

    }

    public function test_returns_error_message_when_no_data_available_for_rally_stages()
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

        $response = $this->getJson('/api/calculate/'.$rally->id);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No stage results available to calculate overall results',
            ]);
        $this->assertTrue(true, 'Tested rally stage results not found case successfully');

    }

    public function test_returns_successful_message_when_data_calculated_or_updated()
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
        $stage2 = Stage::create([
            'rally_id'=>$rally->id,
            'stage_name'=>'JOHN PORK REVERSE',
            'stage_number'=>2,
            'distance_km'=>23.44,
            'start_date'=>$year.'-10-15',
            'start_time'=>'22:44:04',
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

        $stageResult1 = StageResults::create([
            'crew_id'=>$crew->id,
            'stage_id'=>$stage->id,
            'time_taken'=>'06:35.47',
            'avg_speed'=>'104.23'
        ]);
        $stageResult2 = StageResults::create([
            'crew_id'=>$crew->id,
            'stage_id'=>$stage2->id,
            'time_taken'=>'06:35.47',
            'avg_speed'=>'104.23'
        ]);
        $penalty = Penalties::create([
            'crew_id'=>$crew->id,
            'stage_id'=>$stage->id,
            'penalty_type'=>'Jump start at SS-1 (-0.12)',
            'penalty_amount'=>'00:10.000'
        ]);

        $response = $this->getJson('/api/calculate/'.$rally->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Overall results calculated and saved successfully.',
            ]);
        $this->assertTrue(true, 'Tested rally stage results calculated case successfully');

    }

}
