<?php


namespace App\Modules\PublicModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use App\Traits\AzureBlobStorage;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolTeam;
use App\Models\SchoolTeamUser;
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

        
        $school_users = array();
        $media_info = [
            'profile_picture_url' => null,
            'cover_picture_url' => null,
            'media_urls' => array(),
        ];
        $teams =[];
        if($school){
            $school_users = SchoolUser::connect(config('database.secondary'))
            ->join('users', 'users.id', '=' ,'school_users.user_id')
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->leftJoin('players', function($join) {
                $join->on('players.user_id', '=', 'users.id')
                     ->where('user_roles.name', 'player'); // Conditional on user role
            })
            ->leftJoin('coaches', function($join) {
                $join->on('coaches.user_id', '=', 'users.id')
                     ->where('user_roles.name', 'coach'); // Conditional on user role
            })
            ->where('school_users.school_id', $school->id)
            ->select(
                'school_users.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'users.display_name as display_name',
                'users.slug',
                'user_roles.name as user_role',
                'school_users.role as school_user_role',
                'players.id as player_id', // Select player ID
                'coaches.id as coach_id'   // Select coach ID
            )
            ->addSelect(DB::raw('IF((SELECT type FROM coaches WHERE user_id = users.id ) IS NULL,"viewer",(SELECT type FROM coaches WHERE user_id = users.id )) as user_permission_type'))
            ->get();
            // $school_users = SchoolUser::connect(config('database.secondary'))
            //     ->join('users', 'users.id', '=' ,'school_users.user_id')
            //     ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            //     ->where('school_users.school_id', $school->id)
            //     ->select(
            //         'school_users.id',
            //         'users.id as user_id',
            //         'users.first_name',
            //         'users.last_name',
            //         'users.slug',
            //         'user_roles.name as user_role',
            //     )
            //     ->addSelect(DB::raw('IF((SELECT type FROM coaches WHERE user_id = users.id ) IS NULL,"viewer",(SELECT type FROM coaches WHERE user_id = users.id )) as user_permission_type'))
            //     ->get();

            $profile_picture = $this->getSingleFileByEntityId($school->id,'school_profile_picture');
            $cover_picture = $this->getSingleFileByEntityId($school->id,'school_profile_cover');
            $media_urls = $this->getMultipleFilesByEntityId($school->id,'school_profile_media');

            $media_info = [
                'profile_picture' => $profile_picture,
                'cover_picture' => $cover_picture,
                'media_urls' => $media_urls,
            ];

            $teams = SchoolTeam::connect(config('database.secondary'))
                        ->join('schools','schools.id','=','school_teams.school_id')
                        ->where('school_id',$school->id)
                        ->select(
                            'school_teams.id as team_id',
                            'school_teams.name as team_name',
                            'school_teams.school_id',
                            'schools.name as school_name'
                        )
                       ->get();
   
            foreach( $teams as $key=> $data){
                    $team_users = SchoolTeamUser::connect(config('database.secondary'))
                            ->where('school_team_users.team_id',$data->team_id)
                            ->join('users','users.id','=','school_team_users.user_id')
                            ->leftJoin('players','players.id','=','school_team_users.player_id')
                            ->select(
                                'school_team_users.id as id',
                                'school_team_users.user_id as user_id',
                                'school_team_users.status',
                                'school_team_users.player_id',
                                'school_team_users.coache_id',
                                'users.display_name as name',
                               'users.user_role_id as role_id',
                               'players.*'
                            )
                           ->get();
                    $teams[$key]['team_users'] =$team_users;
            }
        }

        return [
            'school_info' => $school,
            'school_users_info' => $school_users,
            'media_info' => $media_info,
            'team_info'  => $teams
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

    public function uploadProfilePicture ($file, $school_slug){
        $school = School::connect(config('database.secondary'))
            ->where('slug', $school_slug)
            ->first();
        $data = null;
        if($school) {
            $data = $this->uploadSingleFile($file, $school->id, 'school_profile_picture');
        }
        return $data;
    }

    public function uploadCoverPicture ($file, $school_slug){
        $school = School::connect(config('database.secondary'))
            ->where('slug', $school_slug)
            ->first();
        $data = null;
        if($school) {
            $data = $this->uploadSingleFile($file, $school->id, 'school_profile_cover');
        }
        return $data;
    }

    public function uploadMedia ($files, $school_slug){
        $school = School::connect(config('database.secondary'))
            ->where('slug', $school_slug)
            ->first();
        $dataArray = array();
        if($school) {
            $dataArray = $this->uploadMultipleFiles($files, $school->id, 'school_profile_media');
        }
        return $dataArray;
    }

    public function removeMedia (array $data, $school_slug){
        $school = School::connect(config('database.secondary'))
            ->where('slug', $school_slug)
            ->first();
        $isRemoved = false;
        if($school) {
            $isRemoved = $this->removeFile($data['media_id']);
        }
        return $isRemoved;
    }
}
