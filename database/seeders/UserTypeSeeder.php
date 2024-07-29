<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            [
                'name' => 'none',
                'short_name' => 'none'
            ],
            [
                'name' => 'Standard',
                'short_name' => 'free'
            ],
            [
                'name' => 'Premium',
                'short_name' => 'premium'
            ],
        ];

        foreach($list as $data)
        {
            \App\Models\UserType::create([
                'name' => $data['name'],
                'short_name' => $data['short_name'],
            ]);
        }
    }
}
