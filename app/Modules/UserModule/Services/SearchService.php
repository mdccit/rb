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
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;
        
        $query = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->leftJoin('players','players.user_id','=','users.id')
            ->leftJoin('user_addresses','user_addresses.user_id','=','users.id')
            ->where('users.id', '!=', auth()->user()->id) //Not included himself/herself
            ->where('users.user_role_id', '!=', config('app.user_roles.default'))
            ->where('users.user_role_id', '!=', config('app.user_roles.admin'))
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'users.email',
                'user_roles.name as user_role',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at',
                'players.*',
                'user_addresses.*'
            );

        if($user_role != 0){
            $query->where('users.user_role_id', $user_role);
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
