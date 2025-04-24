<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Fur1ozz',
                'email' => 'main.admin@admin.com',
                'password' => Hash::make('admin.password.987987'),
            ]
        );

        User::create([
            'name' => 'Test Admin',
            'email' => 'test.admin@example.com',
            'password' => Hash::make('test.password.99'),
        ]);
    }
}
