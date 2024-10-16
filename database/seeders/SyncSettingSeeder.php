<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SyncSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list = [
            [
                'name' => 'name',
                'label' => 'Name',
                'value' => false
            ],
            [
                'name' => 'url',
                'label' => 'Url',
                'value' => true
            ],
            [
                'name' => 'tuition_in_state',
                'label' => 'Tuition in state',
                'value' => true
            ],
            [
                'name' => 'tuition_out_state',
                'label' => 'Tuition out state',
                'value' => true
            ],
            [
                'name' => 'cost_of_attendance',
                'label' => 'Cost of attendance',
                'value' => true
            ],
            [
                'name' => 'degrees_offered',
                'label' => 'Degrees offered',
                'value' => true
            ],
            [
                'name' => 'address',
                'label' => 'Address',
                'value' => true
            ],
            [
                'name' => 'city',
                'label' => 'City',
                'value' => true
            ],
            [
                'name' => 'state',
                'label' => 'State',
                'value' => true
            ],
            [
                'name' => 'zip',
                'label' => 'Zip',
                'value' => true
            ],
            [
                'name' => 'coords_lat',
                'label' => 'Coords lat',
                'value' => true
            ],
            [
                'name' => 'coords_lng',
                'label' => 'Coords lng',
                'value' => true
            ],
            [
                'name' => 'acceptance_rate',
                'label' => 'Acceptance rate',
                'value' => true
            ],
            [
                'name' => 'graduation_rate',
                'label' => 'Graduation rate',
                'value' => true
            ],
            [
                'name' => 'student_count',
                'label' => 'Student count',
                'value' => true
            ],
            [
                'name' => 'earnings_1_year_after_graduation',
                'label' => 'Earnings 1 year after graduation',
                'value' => true
            ],
            [
                'name' => 'earnings_3_years_after_graduation',
                'label' => 'Earnings 3 years after graduation',
                'value' => true
            ],
            [
                'name' => 'student_to_faculty_ratio',
                'label' => 'Student to faculty ratio',
                'value' => true
            ],
            [
                'name' => 'percentage_of_international_students',
                'label' => 'Percentage of international students',
                'value' => true
            ]
           
        ];

        foreach($list as $data)
        {
            \App\Models\SyncSetting::create([
                'name' => $data['name'],
                'label' => $data['label'],
                'value' => $data['value'],
            ]);
        }
    }
}
