<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ExtendedCrewParticipationSeeder extends Seeder
{
    public function run()
    {
        $driveTypeMapping = [
            'AWD' => [1, 2, 3, 5, 9, 10, 11, 13, 20],
            'FWD' => [4, 7, 8, 12, 15, 19],
            'RWD' => [6, 14],
            '2WD' => [21, 24]
        ];

        $rallies = DB::table('rallies')->pluck('id');

        foreach ($rallies as $rallyId) {
            $crews = DB::table('crews')
                ->where('rally_id', $rallyId)
                ->where('is_historic', false)
                ->inRandomOrder()
                ->limit(rand(10, 25))
                ->get();

            foreach ($crews as $crew) {
                $currentClasses = DB::table('crew_class_involvements')->where('crew_id', $crew->id)->pluck('class_id')->toArray();
                $currentGroups = DB::table('crew_group_involvements')->where('crew_id', $crew->id)->pluck('group_id')->toArray();

                $driveType = $crew->drive_type;

                $eligibleClasses = [];

                foreach ($driveTypeMapping as $type => $classIds) {
                    if ($driveType === $type || ($driveType === '2WD' && in_array($type, ['FWD', 'RWD']))) {
                        $eligibleClasses = array_merge($eligibleClasses, $classIds);
                    }
                }

                $eligibleClasses = array_unique($eligibleClasses);

                // Get new class options excluding already assigned ones
                $newClasses = array_diff($eligibleClasses, $currentClasses);
                shuffle($newClasses);
                $newClasses = array_slice($newClasses, 0, rand(1, 3));

                $newGroups = [];

                foreach ($newClasses as $newClass) {
                    $groupId = DB::table('group_classes')->where('id', $newClass)->value('group_id');

                    DB::table('crew_class_involvements')->insert([
                        'crew_id' => $crew->id,
                        'class_id' => $newClass,
                    ]);

                    // If the group isn't already assigned, mark it for later assignment
                    if ($groupId && !in_array($groupId, $currentGroups) && !in_array($groupId, $newGroups)) {
                        $newGroups[] = $groupId;
                    }
                }

                foreach ($newGroups as $newGroup) {
                    DB::table('crew_group_involvements')->insert([
                        'crew_id' => $crew->id,
                        'group_id' => $newGroup,
                    ]);
                }
            }
        }
    }
}
