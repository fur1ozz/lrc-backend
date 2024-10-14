<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Participant;


class ParticipantSeeder extends Seeder
{
    public function run()
    {
        Participant::create([
            'name' => 'John',
            'surname' => 'Doe',
            'desc' => 'A well-known rally participant.',
            'nationality' => 'lv',
            'image' => null,
        ]);

        Participant::create([
            'name' => 'Jane',
            'surname' => 'Smith',
            'desc' => 'An expert rally driver from Estonia.',
            'nationality' => 'ee',
            'image' => null,
        ]);

        Participant::create([
            'name' => 'Bill',
            'surname' => 'Gates',
            'desc' => 'A rally enthusiast from the USA.',
            'nationality' => 'us',
            'image' => null,
        ]);
    }
}
