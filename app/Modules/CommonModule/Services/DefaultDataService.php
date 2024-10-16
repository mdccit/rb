<?php


namespace App\Modules\CommonModule\Services;


use App\Models\Conference;
use App\Models\Country;
use App\Models\Division;
use App\Models\Nationality;
use App\Models\PlayerBudget;

class DefaultDataService
{
    public function getCountries()
    {
        return Country::connect(config('database.secondary'))
            ->select('id as value', 'name as label', 'iso2 as flag', 'fips as short_name', 'phone_code', 'time_zone_in_capital')
            ->get();
    }

    public function getNationalities()
    {
        return Nationality::connect(config('database.secondary'))
            ->select('id as value', 'name as label')
            ->get();
    }

    public function getPlayerBudgets()
    {
        return PlayerBudget::connect(config('database.secondary'))
            ->select('id as value', 'budget_range as label', 'budget_min', 'budget_max')
            ->get();
    }

    public function getConferences()
    {
        return Conference::connect(config('database.secondary'))
            ->select('id as value', 'name as label', 'short_name')
            ->get();
    }

    public function getDivisions()
    {
        return Division::connect(config('database.secondary'))
            ->select('id as value', 'name as label', 'short_name')
            ->get();
    }

    public function getGenders()
    {
        return [
            [
                'label' => 'Male',
                'value' => 'male'
            ],
            [
                'label' => 'Female',
                'value' => 'female'
            ],
            [
                'label' => 'Other',
                'value' => 'other'
            ]
        ];
    }

    public function getHandedness()
    {
        return [
            [
                'label' => 'Left',
                'value' => 'left'
            ],
            [
                'label' => 'Right',
                'value' => 'right'
            ],
            [
                'label' => 'Both',
                '' => 'both'
            ]
        ];
    }

    public function getLanguages()
    {
        $languages = config('ocr.languages');
        $dropdownData = [];
        foreach ($languages as $key => $value) {
            $dropdownData[] = [
                'label' => $value,
                'value' => $key
            ];
        }
        return $dropdownData;
    }
}
