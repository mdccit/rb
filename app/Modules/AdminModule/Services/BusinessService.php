<?php


namespace App\Modules\AdminModule\Services;


use App\Models\Business;
use App\Models\BusinessManager;
use App\Traits\AzureBlobStorage;
use App\Traits\GeneralHelpers;

class BusinessService
{
    use GeneralHelpers;
    use AzureBlobStorage;

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
                'slug',
                'other_data->total_members as total_members',
                'other_data->editors as editors',
                'other_data->viewers as viewers',
                'created_at as joined_at'
            );

        if ($has_admins === 'has_admins') {
            $query->where('other_data->editors', '>',0);
        } elseif ($has_admins === 'no_admins') {
            $query->where('other_data->editors',0);
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
        $business = Business::connect(config('database.secondary'))
            ->where('id', $business_id)
            ->select(
                'id',
                'name',
                'bio',
                'slug',
                'is_verified',
                'is_approved',
                'url',
                'created_at as joined_at',
                'other_data'
            )
            ->first();

        $business_users = array();
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];

        if($business){
            $business_users = BusinessManager::connect(config('database.secondary'))
                ->join('users', 'users.id', '=' ,'business_managers.user_id')
                ->where('business_managers.business_id', $business_id)
                ->select(
                    'business_managers.id',
                    'users.id as user_id',
                    'users.first_name',
                    'users.last_name',
                    'users.slug',
                    'business_managers.type as user_permission_type'
                )
                ->get();

            $profile_picture = $this->getSingleFileByEntityId($business_id,'business_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($business_id,'business_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($business_id,'business_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];
        }

        return [
            'business_info' => $business,
            'business_users_info' => $business_users,
            'media_info' => $media_info,
        ];
    }

    public function createBusiness(array $data){
        Business::connect(config('database.default'))
            ->create([
                'name' => $data['name'],
                'slug' => $this->generateSlug(new Business(), $data['name'], 'slug'),
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
                'slug',
                'is_verified',
                'is_approved',
                'url',
                'created_at as joined_at',
                'other_data'
            )
            ->first();

        $business_users = array();
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];

        if($business){
            $business_users = BusinessManager::connect(config('database.secondary'))
                ->join('users', 'users.id', '=' ,'business_managers.user_id')
                ->where('business_managers.business_id', $business_id)
                ->select(
                    'business_managers.id',
                    'users.id as user_id',
                    'users.first_name',
                    'users.last_name',
                    'users.slug',
                    'business_managers.type as user_permission_type'
                )
                ->get();

            $profile_picture = $this->getSingleFileByEntityId($business_id,'business_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($business_id,'business_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($business_id,'business_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];
        }

        return [
            'business_info' => $business,
            'business_users_info' => $business_users,
            'media_info' => $media_info,
        ];
    }

    public function uploadProfilePicture ($file, $business_id){
        return $this->uploadSingleFile($file, $business_id, 'business_profile_picture');
    }

    public function uploadCoverPicture ($file, $business_id){
        return $this->uploadSingleFile($file, $business_id, 'business_profile_cover');
    }

    public function uploadMedia ($files, $business_id){
        return $this->uploadMultipleFiles($files, $business_id, 'business_profile_media');
    }

    public function removeMedia ($media_id){
        return $this->removeFile($media_id);
    }
}
