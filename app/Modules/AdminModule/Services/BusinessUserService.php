<?php


namespace App\Modules\AdminModule\Services;


use App\Models\Business;
use App\Models\BusinessManager;
use App\Models\User;
use App\Notifications\BusinessManagerApprovalEmailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BusinessUserService
{
    public function getAllBusinessUsers ($business_id){
        return BusinessManager::connect(config('database.secondary'))
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
    }

    public function searchUsers (array $data,$business_id){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->whereNotIn('users.id', DB::table('business_managers')->where('business_id', $business_id)->pluck('user_id')->toArray())
            ->where('user_roles.id', config('app.user_roles.business_manager'))
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

    public function addBusinessUser(array $data)
    {
        BusinessManager::connect(config('database.default'))
            ->where('user_id', $data['user'])
            ->update([
                'business_id' => $data['business'],
                'type' => 'viewer',
                'status' => 'accepted',
            ]);

        $business = Business::connect(config('database.default'))
            ->where('id', $data['business'])
            ->first();
        if($business){

            $total_staff = 1;
            $non_admin_staff = 1;

            $other_data = $business->other_data;
            if($other_data){
                $total_staff += $business->other_data['total_staff'];
                $non_admin_staff += $business->other_data['non_admin_staff'];
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

            $business->update(['other_data' => $other_data]);
        }

        $user = User::connect(config('database.secondary'))
            ->where('id', $data['user'])
            ->first();
        if($user){
            Notification::route('mail',$user->email)->notify(new BusinessManagerApprovalEmailNotification($user));
        }


    }

}
