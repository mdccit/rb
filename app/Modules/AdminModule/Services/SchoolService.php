<?php


namespace App\Modules\AdminModule\Services;


use App\Models\School;
use App\Models\SchoolUser;
use App\Traits\AzureBlobStorage;
use App\Traits\GeneralHelpers;
use Illuminate\Support\Facades\DB;

class SchoolService
{
    use GeneralHelpers;
    use AzureBlobStorage;

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
                'slug',
                'other_data->teams_count as teams_count',
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

        if ($is_connected_to_school === 'connected_to_school') {
            $query->whereNotNull('gov_id');
        } elseif ($is_connected_to_school === 'not_connected_to_school') {
            $query->whereNull('gov_id');
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

    public function getSchool ($school_id){
        $school = School::connect(config('database.secondary'))
            ->where('id', $school_id)
            ->select(
                'id',
                'name',
                'bio',
                'slug',
                'is_verified',
                'is_approved',
                'gov_id',
                'gov_sync_settings',
                'conference_id',
                'division_id',
                'url',
                'genders_recruiting',
                'created_at as joined_at',
                'other_data'
            )
            ->first();

        $school_users = array();
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];

        if($school){
            $school_users = SchoolUser::connect(config('database.secondary'))
                ->join('users', 'users.id', '=' ,'school_users.user_id')
                ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                ->where('school_users.school_id', $school_id)
                ->select(
                    'school_users.id',
                    'users.id as user_id',
                    'users.first_name',
                    'users.last_name',
                    'users.slug',
                    'user_roles.name as user_role',
                )
                ->addSelect(DB::raw('IF((SELECT type FROM coaches WHERE user_id = users.id ) IS NULL,"viewer",(SELECT type FROM coaches WHERE user_id = users.id )) as user_permission_type'))
                ->get();

            $profile_picture = $this->getSingleFileByEntityId($school_id,'school_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($school_id,'school_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($school_id,'school_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];
        }

        return [
            'school_info' => $school,
            'school_users_info' => $school_users,
            'media_info' => $media_info,
        ];
    }

    public function createSchool(array $data){
        School::connect(config('database.default'))
            ->create([
                'name' => $data['name'],
                'slug' => $this->generateSlug(new School(), $data['name'], 'slug'),
            ]);
    }

    public function updateSchool(array $data, $school_id){
        $school = School::connect(config('database.default'))
            ->where('id', $school_id)
            ->first();

        if($school){
            $other_data = $school->other_data;
            if(!$other_data){
                $other_data = [
                    'teams_count' => 0,
                    'total_staff' => 0,
                    'admin_staff' => 0,
                    'non_admin_staff' => 0,
                ];
            }

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

    public function deleteSchool ($school_id){
        School::connect(config('database.default'))
            ->where('id', $school_id)
            ->delete();
    }

    public function viewSchool ($school_id){
        $school = School::connect(config('database.secondary'))
            ->where('id', $school_id)
            ->select(
                'id',
                'name',
                'bio',
                'slug',
                'is_verified',
                'is_approved',
                'gov_id',
                'gov_sync_settings',
                'conference_id',
                'division_id',
                'url',
                'genders_recruiting',
                'created_at as joined_at',
                'other_data'
            )
            ->first();

        $school_users = array();
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];

        if($school){
            $school_users = SchoolUser::connect(config('database.secondary'))
                ->join('users', 'users.id', '=' ,'school_users.user_id')
                ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                ->where('school_users.school_id', $school_id)
                ->select(
                    'school_users.id',
                    'users.id as user_id',
                    'users.first_name',
                    'users.last_name',
                    'users.slug',
                    'user_roles.name as user_role',
                )
                ->addSelect(DB::raw('IF((SELECT type FROM coaches WHERE user_id = users.id ) IS NULL,"viewer",(SELECT type FROM coaches WHERE user_id = users.id )) as user_permission_type'))
                ->get();

            $profile_picture = $this->getSingleFileByEntityId($school_id,'school_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($school_id,'school_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($school_id,'school_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];
        }

        return [
            'school_info' => $school,
            'school_users_info' => $school_users,
            'media_info' => $media_info,
        ];
    }

    public function uploadProfilePicture ($file, $school_id){
        return $this->uploadSingleFile($file, $school_id, 'school_profile_picture');
    }

    public function uploadCoverPicture ($file, $school_id){
        return $this->uploadSingleFile($file, $school_id, 'school_profile_cover');
    }

    public function uploadMedia ($files, $school_id){
        return $this->uploadMultipleFiles($files, $school_id, 'school_profile_media');
    }

    public function removeMedia ($media_id){
        return $this->removeFile($media_id);
    }

}
