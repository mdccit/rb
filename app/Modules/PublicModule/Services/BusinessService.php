<?php


namespace App\Modules\PublicModule\Services;


use App\Models\Business;
use App\Models\BusinessManager;
use App\Models\School;
use App\Models\User;
use App\Traits\AzureBlobStorage;

class BusinessService
{
    use AzureBlobStorage;

    public function getBusinessProfile ($business_slug){
        $business = Business::connect(config('database.secondary'))
            ->where('slug', $business_slug)
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
                ->where('business_managers.business_id', $business->id)
                ->select(
                    'business_managers.id',
                    'users.id as user_id',
                    'users.first_name',
                    'users.last_name',
                    'users.slug',
                    'business_managers.type as management_type'
                )
                ->get();

            $profile_picture = $this->getSingleFileByEntityId($business->id,'business_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($business->id,'business_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($business->id,'business_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];
        }

        return [
            'business_info' => $business,
            'business_managers_info' => $business_users,
            'media_info' => $media_info,
        ];
    }

    public function updateBasicInfo (array $data, $business_slug){
        $business = Business::connect(config('database.default'))
            ->where('slug', $business_slug)
            ->first();
        if($business) {
            $business->update([
                'name' => $data['name'],
            ]);
        }
    }

    public function updateBio (array $data, $business_slug){
        $business = Business::connect(config('database.default'))
            ->where('slug', $business_slug)
            ->first();
        if($business) {
            $business->update([
                'bio' => $data['bio'],
            ]);
        }
    }


    public function uploadProfilePicture ($file, $business_slug){
        $business = Business::connect(config('database.secondary'))
            ->where('slug', $business_slug)
            ->first();
        $data = null;
        if($business) {
            $data = $this->uploadSingleFile($file, $business->id, 'business_profile_picture');
        }
        return $data;
    }

    public function uploadCoverPicture ($file, $business_slug){
        $business = Business::connect(config('database.secondary'))
            ->where('slug', $business_slug)
            ->first();
        $data = null;
        if($business) {
            $data = $this->uploadSingleFile($file, $business->id, 'business_profile_cover');
        }
        return $data;
    }

    public function uploadMedia ($files, $business_slug){
        $business = Business::connect(config('database.secondary'))
            ->where('slug', $business_slug)
            ->first();
        $dataArray = array();
        if($business) {
            $dataArray = $this->uploadMultipleFiles($files, $business->id, 'business_profile_media');
        }
        return $dataArray;
    }

    public function removeMedia (array $data, $business_slug){
        $business = Business::connect(config('database.secondary'))
            ->where('slug', $business_slug)
            ->first();
        $isRemoved = false;
        if($business) {
            $isRemoved = $this->removeFile($data['media_id']);
        }
        return $isRemoved;
    }
}
