<?php


namespace App\Modules\PublicModule\Services;


use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use App\Traits\AzureBlobStorage;

class SchoolService
{
    use AzureBlobStorage;

    public function getSchoolProfile ($school_slug){
        $school = School::connect(config('database.secondary'))
            ->where('slug', $school_slug)
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

        $school_users = SchoolUser::connect(config('database.secondary'))
            ->join('users', 'users.id', '=' ,'school_users.user_id')
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->where('school_users.school_id', $school->id)
            ->select(
                'school_users.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'users.slug',
                'user_roles.name as user_role',
                'school_users.role as school_user_role'
            )
            ->get();

        return [
            'school_info' => $school,
            'school_users_info' => $school_users,
        ];
    }

    public function destroy($id){
        SchoolUser::connect(config('database.default'))->destroy($id);
    }

    public function updateBasicInfo (array $data, $school_slug){
        $school = School::connect(config('database.default'))
            ->where('slug', $school_slug)
            ->first();
        if($school) {
            $school->update([
                'name' => $data['name'],
            ]);
        }
    }

    public function updateBio (array $data, $school_slug){
        $school = School::connect(config('database.default'))
            ->where('slug', $school_slug)
            ->first();
        if($school) {
            $school->update([
                'bio' => $data['bio'],
            ]);
        }
    }

    public function addNewAcademic (array $data, $school_slug){
        $this ->getOtherData($school_slug);

        $school = School::connect(config('database.default'))
            ->where('slug', $school_slug)
            ->first();
        if($school) {
            $academics = array();
            if (array_key_exists("academics",$school['other_data'])){
                $academics = $school['other_data']['academics'];
            }
            array_push($academics,$data['academic']);
            $school->update([
                'other_data->academics' => $academics,
            ]);
        }
    }

    public function removeAcademic (array $data, $school_slug){
        $this ->getOtherData($school_slug);

        $school = School::connect(config('database.default'))
            ->where('slug', $school_slug)
            ->first();
        if($school) {
            if (array_key_exists("academics",$school['other_data'])){
                $academics = $school['other_data']['academics'];
                $updated_academics = array_diff($academics,[$data['academic']]);
                $school->update([
                    'other_data->academics' => $updated_academics,
                ]);
            }
        }
    }

    public function updateTennisInfo (array $data, $school_slug){
        $school = School::connect(config('database.default'))
            ->where('slug', $school_slug)
            ->first();
        if($school) {
            $this ->getOtherData($school_slug);
            $school->update([
                'conference_id' => $data['conference'],
                'division_id' => $data['division'],
                'other_data->average_utr' => $data['average_utr'],
            ]);
        }
    }

    public function updateStatusInfo (array $data, $school_slug){
        $school = School::connect(config('database.default'))
            ->where('slug', $school_slug)
            ->first();
        if($school) {
            $this ->getOtherData($school_slug);
            $school->update([
                'other_data->tuition_in_of_state' => $data['tuition_in_of_state'],
                'other_data->tuition_out_of_state' => $data['tuition_out_of_state'],
                'other_data->cost_of_attendance' => $data['cost_of_attendance'],
                'other_data->graduation_rate' => $data['graduation_rate'],
            ]);
        }
    }

    private function getOtherData($school_slug){
        $school = School::connect(config('database.default'))
            ->where('slug', $school_slug)
            ->whereNull('other_data')
            ->first();
        if($school){
            $other_data = [
                'teams_count' => 0,
                'total_staff' => 0,
                'admin_staff' => 0,
                'non_admin_staff' => 0,
                'academics' => null,
                'average_utr' => 0,
                'tuition_in_of_state' => 0,
                'tuition_out_of_state' => 0,
                'cost_of_attendance' => 0,
                'graduation_rate' => 0,
            ];

            $school->update([
                'other_data' => $other_data,
            ]);
        }
    }

    public function uploadProfilePicture ($file, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        $data = null;
        if($user) {
            $data = $this->uploadSingleFile($file, $user->id, 'school_profile_picture');
        }
        return $data;
    }

    public function uploadCoverPicture ($file, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        $data = null;
        if($user) {
            $data = $this->uploadSingleFile($file, $user->id, 'school_profile_cover');
        }
        return $data;
    }

    public function uploadMedia ($files, $user_slug){
        $user = User::connect(config('database.default'))
            ->where('slug', $user_slug)
            ->first();
        $dataArray = array();
        if($user) {
            $dataArray = $this->uploadMultipleFiles($files, $user->id, 'school_profile_media');
        }
        return $dataArray;
    }

    public function removeMedia ($media_id){
        return $this->removeFile($media_id);
    }
}
