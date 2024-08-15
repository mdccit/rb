<?php


namespace App\Modules\AdminModule\Services;


use App\Models\Coach;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchoolUserService
{
    public function getAllSchoolUsers ($school_id){
        return SchoolUser::connect(config('database.secondary'))
            ->join('users', 'users.id', '=' ,'school_users.user_id')
            ->join('coaches', 'coaches.user_id', '=' ,'school_users.user_id')
            ->where('school_users.school_id', $school_id)
            ->select(
                'school_users.id',
                'users.id as user_id',
                'users.first_name',
                'users.last_name',
                'coaches.position',
                'school_users.role as school_user_role'
            )
            ->get();
    }

    public function searchUsers (array $data,$school_id){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = User::connect(config('database.secondary'))
            ->join('coaches', 'coaches.user_id', '=' ,'users.id')
            ->whereNotIn('users.id', DB::table('school_users')->where('school_id', $school_id)->pluck('user_id')->toArray())
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'coaches.position'
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
            $total_staff = $school->other_data['total_staff'] + 1;
            $non_admin_staff = $school->other_data['non_admin_staff']+1;

            $school->update([
                'other_data->>total_staff' => $total_staff,
                'other_data->>non_admin_staff' => $non_admin_staff,
            ]);
        }

        $coach = Coach::connect(config('database.default'))
            ->where('user_id', $data['user'])->first();
        if($coach){
            $coach->update([
                'type' => 'viewer',
            ]);
        }
    }
}
