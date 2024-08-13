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
                'budget_min' => '5000',
                'budget_max' => '10000'
            ],
            [
                'budget_range' => '10,000 - 15,000 per year',
                'budget_min' => '10000',
                'budget_max' => '15000'
            ],
            [
                'budget_range' => '15,000 - 20,000 per year',
                'budget_min' => '15000',
                'budget_max' => '20000'
            ],
            [
                'budget_range' => '20,000 - 25,000 per year',
                'budget_min' => '20000',
                'budget_max' => '25000'
            ],
            [
                'budget_range' => '25,000 - 30,000 per year',
                'budget_min' => '25000',
                'budget_max' => '30000'
            ],
            [
                'budget_range' => '30,000 - 35,000 per year',
                'budget_min' => '30000',
                'budget_max' => '35000'
            ],
            [
                'budget_range' => '35,000 - 40,000 per year',
                'budget_min' => '35000',
                'budget_max' => '40000'
            ],
            [
                'budget_range' => '40,000 - 45,000 per year',
                'budget_min' => '40000',
                'budget_max' => '45000'
            ],
            [
                'budget_range' => '45,000 - 50,000 per year',
                'budget_min' => '45000',
                'budget_max' => '50000'
            ],
            [
                'budget_range' => '50,000 - 60,000 per year',
                'budget_min' => '50000',
                'budget_max' => '60000'
            ],
            [
                'budget_range' => '60,000 - 70,000 per year',
                'budget_min' => '60000',
                'budget_max' => '70000'
            ],
            [
                'budget_range' => '70,000 - 80,000 per year',
                'budget_min' => '70000',
                'budget_max' => '80000'
            ],
            [
                'budget_range' => '80,000 - 90,000 per year',
                'budget_min' => '80000',
                'budget_max' => '90000'
            ],
            [
                'budget_range' => '90,000 - 100,000 per year',
                'budget_min' => '90000',
                'budget_max' => '100000'
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
