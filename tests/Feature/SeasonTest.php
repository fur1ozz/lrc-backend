<?php

namespace Tests\Feature;

use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SeasonTest extends TestCase
{
    use RefreshDatabase;
    public function test_it_can_insert_a_season()
    {
        $year = 2024;

        $season = Season::create(['year' => $year]);

        $this->assertDatabaseHas('seasons', [
            'year' => $year,
        ]);
    }
}
