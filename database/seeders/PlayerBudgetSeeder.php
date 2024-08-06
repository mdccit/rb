<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlayerBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            [
                'budget_range' => '5,000 - 10,000 per year',
                'budget_min' => '5',
                'budget_max' => '10'
            ],
            [
                'budget_range' => '10,000 - 15,000 per year',
                'budget_min' => '10',
                'budget_max' => '15'
            ],
            [
                'budget_range' => '15,000 - 20,000 per year',
                'budget_min' => '15',
                'budget_max' => '20'
            ],
            [
                'budget_range' => '20,000 - 25,000 per year',
                'budget_min' => '20',
                'budget_max' => '25'
            ],
            [
                'budget_range' => '25,000 - 30,000 per year',
                'budget_min' => '25',
                'budget_max' => '30'
            ],
            [
                'budget_range' => '30,000 - 35,000 per year',
                'budget_min' => '30',
                'budget_max' => '35'
            ],
            [
                'budget_range' => '35,000 - 40,000 per year',
                'budget_min' => '35',
                'budget_max' => '40'
            ],
            [
                'budget_range' => '40,000 - 45,000 per year',
                'budget_min' => '40',
                'budget_max' => '45'
            ],
            [
                'budget_range' => '45,000 - 50,000 per year',
                'budget_min' => '45',
                'budget_max' => '50'
            ],
            [
                'budget_range' => '50,000 - 60,000 per year',
                'budget_min' => '50',
                'budget_max' => '60'
            ],
            [
                'budget_range' => '60,000 - 70,000 per year',
                'budget_min' => '60',
                'budget_max' => '70'
            ],
            [
                'budget_range' => '70,000 - 80,000 per year',
                'budget_min' => '70',
                'budget_max' => '80'
            ],
            [
                'budget_range' => '80,000 - 90,000 per year',
                'budget_min' => '80',
                'budget_max' => '90'
            ],
            [
                'budget_range' => '90,000 - 100,000 per year',
                'budget_min' => '90',
                'budget_max' => '100'
            ],
        ];

        foreach($list as $data)
        {
            \App\Models\PlayerBudget::create([
                'currency_id' => config('app.currencies.default'),
                'budget_range' => $data['budget_range'],
                'budget_min' => $data['budget_min'],
                'budget_max' => $data['budget_max'],
            ]);
        }
    }
}
