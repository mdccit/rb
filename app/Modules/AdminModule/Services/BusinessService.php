<?php


namespace App\Modules\AdminModule\Services;


use App\Models\Business;
use App\Models\BusinessManager;

class BusinessService
{
    public function getAllBusinesses (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $has_admins = array_key_exists("has_admins",$data)?$data['has_admins']:'none';
        $is_verified = array_key_exists("is_verified",$data)?$data['is_verified']:'none';
        $has_coordinates = array_key_exists("has_coordinates",$data)?$data['has_coordinates']:'none';
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = Business::connect(config('database.secondary'))
            ->select(
                'id',
                'name',
                'bio',
                'other_data->total_staff as total_staff',
                'other_data->admin_staff as admin_staff',
                'other_data->non_admin_staff as non_admin_staff',
                'created_at as joined_at'
            );

        if ($has_admins === 'has_admins') {
            $query->where('other_data->admin_staff', '>',0);
        } elseif ($has_admins === 'no_admins') {
            $query->where('other_data->admin_staff',0);
        }

        if ($is_verified === 'verified') {
            $query->where('is_verified', true);
        } elseif ($is_verified === 'not_verified') {
            $query->where('is_verified', false);
        }

        if ($has_coordinates === 'has_coordinates') {
            $query->where('other_data->has_coordinates', true);
        } elseif ($has_coordinates === 'no_coordinates') {
            $query->where('other_data->has_coordinates', false);
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

    public function getBusiness ($business_id){
        return Business::connect(config('database.secondary'))
            ->where('id', $business_id)
            ->select(
                'id',
                'name',
                'bio',
                'is_verified',
                'is_approved',
                'url',
                'created_at as joined_at',
                'other_data'
            )
            ->first();
    }

    public function createBusiness(array $data){
        Business::connect(config('database.default'))
            ->create([
                'name' => $data['name'],
            ]);
    }

    public function updateBusiness(array $data, $business_id){
        $business = Business::connect(config('database.default'))
            ->where('id', $business_id)
            ->first();

        if($business){
            $other_data = $business->other_data;
            if(!$other_data){
                $other_data = [
                    'total_staff' => 0,
                    'admin_staff' => 0,
                    'non_admin_staff' => 0,
                ];
            }

            $business->update([
                'name' => $data['name'],
                'bio' => $data['bio'],
                'is_approved' => $data['is_approved'],
                'is_verified' => $data['is_verified'],
                'other_data' => $other_data
            ]);
        }
    }

    public function deleteBusiness ($business_id){
        Business::connect(config('database.default'))
            ->where('id', $business_id)
            ->delete();
    }

    public function viewBusiness ($business_id){
        $business = Business::connect(config('database.secondary'))
            ->where('id', $business_id)
            ->select(
                'id',
                'name',
                'bio',
                'is_verified',
                'is_approved',
                'url',
                'created_at as joined_at',
                'other_data'
            )
            ->first();

        $business_users = BusinessManager::connect(config('database.secondary'))
            ->join('users', 'users.id', '=' ,'business_managers.user_id')
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->where('business_managers.business_id', $business_id)
            ->select(
                'business_managers.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'user_roles.name as user_role',
                'business_managers.type as business_user_role'
            )
            ->get();

        $dataSet = [
            'business' => $business,
            'business_users' => $business_users,
        ];
        return $dataSet;
    }
}
