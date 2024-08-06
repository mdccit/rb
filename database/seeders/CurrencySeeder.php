<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            [
                'name' => 'US Dollar',
                'short_name' => 'USD',
                'symbol' => '$'
            ]
        ];

        foreach($list as $data)
        {
            \App\Models\Currency::create([
                'name' => $data['name'],
                'short_name' => $data['short_name'],
                'symbol' => $data['symbol'],
            ]);
        }
    }
}
