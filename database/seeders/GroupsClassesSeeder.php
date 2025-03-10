<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsClassesSeeder extends Seeder
{
    public function run()
    {
        $groups = [
            ['group_name' => 'LRC'],
            ['group_name' => 'ERC'],
            ['group_name' => 'RSK'],
            ['group_name' => 'VRK'],
            ['group_name' => 'LARSC'],
        ];

        DB::table('groups')->insert($groups);
        $groupIds = DB::table('groups')->pluck('id', 'group_name')->toArray();

        $classes = [
            'LRC' => ['LRC1', 'LRC2', 'LRC3', 'LRC4', 'LRC5', 'LRC6', 'LRC7', 'Jun'],
            'ERC' => ['EMV1', 'EMV2', 'EMV3', 'EMV4', 'EMV5', 'EMV6', 'EMV7', 'EMV8', 'EMV9', 'EMV Lada', 'Jun'],
            'RSK' => ['RSK8', 'RSK9', 'RSK10'],
            'VRK' => ['VRK11', 'VRK12', 'VRK13'],
            'LARSC' => ['2WD', 'SG-2', 'SG-3', 'SG-4', 'Open', 'Jun'],
        ];

        foreach ($classes as $groupName => $classNames) {
            foreach ($classNames as $className) {
                DB::table('group_classes')->insert([
                    'group_id' => $groupIds[$groupName],
                    'class_name' => $className,
                ]);
            }
        }
    }
}
