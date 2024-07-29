<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            [
                'name' => 'Default',
                'short_name' => 'default'
            ],
            [
                'name' => 'Admin',
                'short_name' => 'admin'
            ],
            [
                'name' => 'Operator',
                'short_name' => 'operator'
            ],
            [
                'name' => 'Player',
                'short_name' => 'player'
            ],
            [
                'name' => 'Coach',
                'short_name' => 'coach'
            ],
            [
                'name' => 'Business Manager',
                'short_name' => 'business_manager'
            ],
            [
                'name' => 'Parent',
                'short_name' => 'parent'
            ],
        ];

        foreach($list as $data)
        {
            \App\Models\UserRole::create([
                'name' => $data['name'],
                'short_name' => $data['short_name'],
            ]);
        }

    }
}
