<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsSeeder extends Seeder
{
    public function run()
    {
        $groups = [
            ['group_name' => 'LRC', 'season' => '2024'],
            ['group_name' => 'ERC', 'season' => '2024'],
            ['group_name' => 'RSK', 'season' => '2024'],
            ['group_name' => 'VRK', 'season' => '2024'],
            ['group_name' => 'LARSC', 'season' => '2024'],
        ];

        DB::table('groups')->insert($groups);
    }
}
