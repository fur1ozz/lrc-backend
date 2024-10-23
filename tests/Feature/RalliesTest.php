<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RalliesTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_correct_rallies_json_structure(): void
    {
        $response = $this->get('/api/rallies');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => [
                'id',
                'rally_name',
                'date_from',
                'date_to',
                'location',
                'road_surface',
                'rally_tag',
                'rally_sequence',
                'season',
            ]
        ]);
    }
}
