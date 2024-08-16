<?php


namespace App\Modules\AdminModule\Services;


use App\Models\School;

class SchoolService
{
    public function getAllSchools (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $has_admins = array_key_exists("has_admins",$data)?$data['has_admins']:'none';
        $is_verified = array_key_exists("is_verified",$data)?$data['is_verified']:'none';
        $is_connected_to_school = array_key_exists("is_connected_to_school",$data)?$data['is_connected_to_school']:'none';
        $has_coordinates = array_key_exists("has_coordinates",$data)?$data['has_coordinates']:'none';
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = School::connect(config('database.secondary'))
            ->select(
                'id',
                'name',
                'bio',
                'other_data->>teams_count as teams_count',
                'other_data->>total_staff as total_staff',
                'other_data->>admin_staff as admin_staff',
                'other_data->>non_admin_staff as non_admin_staff',
                'created_at as joined_at'
            );

        if ($has_admins === 'has_admins') {
            $query->where('other_data->>admin_staff', '>',0);
        } elseif ($has_admins === 'no_admins') {
            $query->where('other_data->>admin_staff',0);
        }

        if ($is_verified === 'verified') {
            $query->where('is_verified', true);
        } elseif ($is_verified === 'not_verified') {
            $query->where('is_verified', false);
        }

        if ($is_connected_to_school === 'connected_to_school') {
            $query->whereNotNull('gov_id');
        } elseif ($is_connected_to_school === 'not_connected_to_school') {
            $query->whereNull('gov_id');
        }

        if ($has_coordinates === 'has_coordinates') {
            $query->where('other_data->>has_coordinates', true);
        } elseif ($has_coordinates === 'no_coordinates') {
            $query->where('other_data->>has_coordinates', false);
        }

        if ($search_key != null) {
            $query->where('name', 'LIKE', '%' . $search_key . '%');
        }


        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate($per_page_items);
        }else{
            $dataSet = $query->get();
        }

        return $dataSet;

    }

    public function getSchool ($school_id){
        return School::connect(config('database.secondary'))
            ->where('id', $school_id)
            ->select(
                'id',
                'name',
                'bio',
                'is_verified',
                'is_approved',
                'gov_id',
                'gov_sync_settings',
                'url',
                'genders_recruiting',
                'created_at as joined_at',
                'other_data'
            )
            ->first();
    }

    public function createSchool(array $data){
        School::connect(config('database.default'))
            ->create([
                'name' => $data['name'],
            ]);
    }

    public function updateSchool(array $data, $school_id){
        $school = School::connect(config('database.default'))
            ->where('id', $school_id)
            ->first();

        if($school){
            $other_data = [
                'teams_count' => 0,
                'total_staff' => 0,
                'admin_staff' => 0,
                'non_admin_staff' => 0,
            ];

            $school->update([
                'name' => $data['name'],
                'bio' => $data['bio'],
                'is_approved' => $data['is_approved'],
                'is_verified' => $data['is_verified'],
                'conference_id' => $data['conference'],
                'division_id' => $data['division'],
                'other_data' => $other_data
            ]);
        }
    }


}
