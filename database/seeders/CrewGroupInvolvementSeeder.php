<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrewGroupInvolvementSeeder extends Seeder
{
    public function run()
    {
        $crewGroupInvolvements = [
            ['crew_id' => 1, 'group_id' => 1], // Crew 1 in Group LRC
            ['crew_id' => 1, 'group_id' => 2], // Crew 1 in Group ERC
            ['crew_id' => 2, 'group_id' => 3], // Crew 2 in Group RSK
            ['crew_id' => 2, 'group_id' => 4], // Crew 2 in Group VRK
            ['crew_id' => 3, 'group_id' => 5], // Crew 3 in Group LARSC
            ['crew_id' => 3, 'group_id' => 2], // Crew 3 in Group ERC
        ];

        DB::table('crew_group_involvements')->insert($crewGroupInvolvements);
    }
}
