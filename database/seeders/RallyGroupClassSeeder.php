<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RallyGroupClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rallies = DB::table('rallies')->pluck('id');
        $groups = DB::table('groups')->pluck('id');
        $classes = DB::table('group_classes')->pluck('id');

        // Assign every group to every rally
        foreach ($rallies as $rallyId) {
            foreach ($groups as $groupId) {
                DB::table('rally_groups')->insert([
                    'rally_id' => $rallyId,
                    'group_id' => $groupId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Assign every class to every rally
        foreach ($rallies as $rallyId) {
            foreach ($classes as $classId) {
                DB::table('rally_classes')->insert([
                    'rally_id' => $rallyId,
                    'class_id' => $classId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
