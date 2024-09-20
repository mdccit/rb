<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sport;
use App\Models\Player;
use App\Models\Coach;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            [
                'name' => 'Tennis',
            ]
        ];

        foreach($list as $data)
        {
            $sport = Sport::create([
                'name' => $data['name'],
            ]);

            Player::connect(config('database.default'))
                ->whereNull('sport_id')
                ->update([
                    'sport_id' => $sport->id,
                ]);
            Coach::connect(config('database.default'))
                ->whereNull('sport_id')
                ->update([
                    'sport_id' => $sport->id,
                ]);
        }
    }
}
