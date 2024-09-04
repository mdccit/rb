<?php


namespace App\Modules\UserModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\Country;
use App\Models\Player;
use App\Models\User;
use App\Models\UserPhone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SearchService
{
    public function search (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $user_role = array_key_exists("user_role",$data)?$data['user_role']:0;
        $is_email_verified = array_key_exists("is_email_verified",$data)?$data['is_email_verified']:'none';
        $last_seen_at = array_key_exists("last_seen_at",$data)?$data['last_seen_at']:null;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $query = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->where('users.id', '!=', auth()->user()->id) //Not included himself/herself
            ->where('users.user_role_id', '!=', config('app.user_roles.default'))
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'users.email',
                'user_roles.name as user_role',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at'
            );

        if($user_role != 0){
            $query->where('users.user_role_id', $user_role);
        }

        if ($is_email_verified === 'verified') {
            $query->whereNotNull('users.email_verified_at');
        } elseif ($is_email_verified === 'not_verified') {
            $query->whereNull('users.email_verified_at');
        }

        if ($last_seen_at != null) {
            $query->where('users.last_logged_at', '>=', now()->subDays($last_seen_at));
        }

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

    
   
    

}
