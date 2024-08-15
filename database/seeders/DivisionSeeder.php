<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            'division_i' => 'Division I',
            'division_ii' => 'Division II',
            'division_iii' => 'Division III',
            'naia' => 'NAIA',
            'njcaa' => 'NJCAA'
        ];

        $keyList = array_keys($list);

        foreach($keyList as $data)
        {
            \App\Models\Division::create([
                'name' => $list[$data],
                'short_name' => $data
            ]);
        }
    }
}
