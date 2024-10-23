<?php

namespace Tests\Feature;

use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SeasonTest extends TestCase
{
    use RefreshDatabase;
    public function test_season_can_be_inserted_in_seasons_table()
    {
        $year = 2024;

        $season = Season::create(['year' => $year]);

        $this->assertDatabaseHas('seasons', [
            'year' => $year,
        ]);
    }
}
