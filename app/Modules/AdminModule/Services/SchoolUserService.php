<?php


namespace App\Modules\AdminModule\Services;


use App\Models\Coach;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use App\Notifications\CoachApprovalEmailNotification;
use App\Notifications\ForgotPasswordEmailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SchoolUserService
{
    public function getAllSchoolUsers ($school_id){
        return SchoolUser::connect(config('database.secondary'))
            ->join('users', 'users.id', '=' ,'school_users.user_id')
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->where('school_users.school_id', $school_id)
            ->select(
                'school_users.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'user_roles.name as user_role',
                'school_users.role as school_user_role'
            )
            ->get();
    }

    public function searchUsers (array $data,$school_id){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->whereNotIn('users.id', DB::table('school_users')->where('school_id', $school_id)->pluck('user_id')->toArray())
            ->whereIn('user_roles.id', array(config('app.user_roles.player'), config('app.user_roles.coach')))
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'user_roles.name as user_role'
            );

        if ($search_key != null) {
            $query->where('users.display_name', 'LIKE', '%' . $search_key . '%');
        }


        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate($per_page_items);
        }else{
            $dataSet = $query->get();
        }

        return $dataSet;
    }

    public function addSchoolUser(array $data)
    {
        SchoolUser::connect(config('database.default'))
            ->create([
                'school_id' => $data['school'],
                'user_id' => $data['user'],
            ]);

        $school = School::connect(config('database.default'))
            ->where('id', $data['school'])
            ->first();
        if($school){

            $total_staff = 1;
            $non_admin_staff = 1;

            $other_data = $school->other_data;
            if($other_data){
                $total_staff += $school->other_data['total_staff'];
                $non_admin_staff += $school->other_data['non_admin_staff'];
            }else{
                $other_data = [
                    'teams_count' => 0,
                    'total_staff' => 0,
                    'admin_staff' => 0,
                    'non_admin_staff' => 0,
                ];
            }
            $other_data['total_staff'] = $total_staff;
            $other_data['non_admin_staff'] = $non_admin_staff;

            $school->update(['other_data' => $other_data]);
        }

        $coach = Coach::connect(config('database.default'))
            ->where('user_id', $data['user'])->first();
        if($coach){
            $coach->update([
                'type' => 'viewer',
            ]);

            $user = User::connect(config('database.secondary'))
                ->where('id', $data['user'])
                ->first();
            if($user){
                Notification::route('mail',$user->email)->notify(new CoachApprovalEmailNotification($user));
            }
        }


    }
}
