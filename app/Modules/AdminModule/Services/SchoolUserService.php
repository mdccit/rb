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
                'users.slug',
                'user_roles.name as user_role',

            )
            ->addSelect(DB::raw('IF((SELECT type FROM coaches WHERE user_id = users.id ) IS NULL,"viewer",(SELECT type FROM coaches WHERE user_id = users.id )) as user_permission_type'))
            ->get();
    }

//    public function getManageSchoolUser ($user_id, $school_id){
//        return SchoolUser::connect(config('database.secondary'))
//            ->join('users', 'users.id', '=' ,'school_users.user_id')
//            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
//            ->where('school_users.user_id', $user_id)
//            ->where('school_users.school_id', $school_id)
//            ->select(
//                'school_users.id',
//                'users.id as user_id',
//                'users.first_name',
//                'users.last_name',
//                'users.slug',
//                'user_roles.name as user_role',
//            )
//            ->get();
//    }

    public function searchUsers (array $data,$school_id){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->whereNotIn('users.id', DB::table('school_users')->pluck('user_id')->toArray())
//            ->whereNotIn('users.id', DB::table('school_users')->where('school_id', $school_id)->pluck('user_id')->toArray())
            ->whereIn('user_roles.id', array(config('app.user_roles.player'), config('app.user_roles.coach')))
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'user_roles.name as user_role'
            );

        if ($search_key != null) {
            $query->whereIn('users.id', DB::table('users')
                ->where('display_name', 'LIKE', '%' . $search_key . '%')
                ->orWhere('email', 'LIKE', '%' . $search_key . '%')
                ->pluck('id')->toArray());
//            $query->where('users.display_name', 'LIKE', '%' . $search_key . '%');
//            $query->orWhere('users.email', 'LIKE', '%' . $search_key . '%');
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
            $this ->getOtherData($school->id);

            $total_members = SchoolUser::connect(config('database.secondary'))
                ->where('school_id', $data['school'])
                ->count();
            $editors = Coach::connect(config('database.secondary'))
                ->where('type', config('app.user_permission_type.editor'))
                ->where('school_id', $data['school'])
                ->count();
            $viewers = $total_members - $editors;

            $other_data = $school->other_data;
            $other_data['total_members'] = $total_members;
            $other_data['editors'] = $editors;
            $other_data['viewers'] = $viewers;

            $school->update(['other_data' => $other_data]);
        }

        $coach = Coach::connect(config('database.default'))
            ->where('user_id', $data['user'])->first();
        if($coach){
            $coach->update([
                'school_id' => $data['school'],
                'type' => 'viewer',
                'status' => 'accepted',
            ]);

            $user = User::connect(config('database.secondary'))
                ->where('id', $data['user'])
                ->first();
            if($user){
                Notification::route('mail',$user->email)->notify(new CoachApprovalEmailNotification($user));
            }
        }


    }

    public function updateSchoolUserManageType(array $data, $user_id)
    {
        $coach = Coach::connect(config('database.default'))
            ->where('user_id', $user_id)
            ->where('school_id', $data['school'])
            ->first();
        if($coach){
            $coach->update([
                'type' => $data['user_permission_type'],
            ]);

            $school = School::connect(config('database.default'))
                ->where('id', $data['school'])
                ->first();
            if($school){
                $this ->getOtherData($school->id);

                $total_members = SchoolUser::connect(config('database.secondary'))
                    ->where('school_id', $data['school'])
                    ->count();
                $editors = Coach::connect(config('database.secondary'))
                    ->where('type', config('app.user_permission_type.editor'))
                    ->where('school_id', $data['school'])
                    ->count();
                $viewers = $total_members - $editors;

                $other_data = $school->other_data;
                $other_data['total_members'] = $total_members;
                $other_data['editors'] = $editors;
                $other_data['viewers'] = $viewers;

                $school->update(['other_data' => $other_data]);
            }
        }
    }

    public function removeSchoolUser(array $data, $user_id)
    {
        $school_user = SchoolUser::connect(config('database.default'))
            ->where('user_id', $user_id)
            ->where('school_id', $data['school'])
            ->first();
        if($school_user){
            $school_user->delete();
        }

        $coach = Coach::connect(config('database.default'))
            ->where('user_id', $user_id)
            ->where('school_id', $data['school'])
            ->first();
        if($coach){
            $coach->update([
                'type' => 'none',
                'school_id' => null,
                'status' => 'cancelled'
            ]);
        }

        $school = School::connect(config('database.default'))
            ->where('id', $data['school'])
            ->first();
        if($school){
            $this ->getOtherData($school->id);

            $total_members = SchoolUser::connect(config('database.secondary'))
                ->where('school_id', $data['school'])
                ->count();
            $editors = Coach::connect(config('database.secondary'))
                ->where('type', config('app.user_permission_type.editor'))
                ->where('school_id', $data['school'])
                ->count();
            $viewers = $total_members - $editors;

            $other_data = $school->other_data;
            $other_data['total_members'] = $total_members;
            $other_data['editors'] = $editors;
            $other_data['viewers'] = $viewers;

            $school->update(['other_data' => $other_data]);
        }
    }

    private function getOtherData($school_id){
        $school = School::connect(config('database.default'))
            ->where('id', $school_id)
            ->whereNull('other_data')
            ->first();
        if($school){
            $other_data = [
                'teams_count' => 0,
                'total_members' => 0,
                'editors' => 0,
                'viewers' => 0,
                'academics' => array(),
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
}
