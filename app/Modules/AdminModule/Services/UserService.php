<?php


namespace App\Modules\AdminModule\Services;


use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\Country;
use App\Models\Player;
use App\Models\User;
use App\Models\UserPhone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function getAllUsers (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $user_role = array_key_exists("user_role",$data)?$data['user_role']:0;
        $is_email_verified = array_key_exists("is_email_verified",$data)?$data['is_email_verified']:'none';
        $last_seen_at = array_key_exists("last_seen_at",$data)?$data['last_seen_at']:null;

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


        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate(2);
        }else{
            $dataSet = $query->get();
        }

        return $dataSet;

    }
    public function createUser(array $data){
        $user = User::connect(config('database.default'))
            ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'display_name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'user_role_id' => $data['user_role'],
                'user_type_id' => config('app.user_types.free'),
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(10)
            ]);
        $user_phone = UserPhone::connect(config('database.secondary'))
            ->where('user_id', $user->id)->first();
        $phone_code = Country::connect(config('database.secondary'))->find($data['phone_code_country'])->getPhoneCode();
        if(!$user_phone){
            UserPhone::connect(config('database.default'))
                ->create([
                    'user_id' => $user->id,
                    'country_id' => $data['phone_code_country'],
                    'is_default' => true,
                    'phone_code' => $phone_code,
                    'phone_number' => $data['phone_number'],
                ]);
        }

        if($data['is_set_email_verified']){
            User::connect(config('database.default'))
                ->where('id', $user->id)
                ->update([
                    'email_verified_at' => Carbon::now(),
                ]);
        }else{
            $user->sendEmailVerificationNotification();
        }

        //Player
        if($data['user_role'] == config('app.user_roles.player')){
            $player = Player::connect(config('database.secondary'))
                ->where('user_id', $user->id)->first();
            if(!$player){
                Player::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                    ]);
            }
        }

        //Coach
        if($data['user_role'] == config('app.user_roles.coach')){
            $coach = Coach::connect(config('database.secondary'))
                ->where('user_id', $user->id)->first();
            if(!$coach){
                Coach::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                    ]);
            }
        }

        //Business Manager
        if($data['user_role'] == config('app.user_roles.business_manager')){
            $business_manager = BusinessManager::connect(config('database.secondary'))
                ->where('user_id', $user->id)->first();
            if(!$business_manager){
                BusinessManager::connect(config('database.default'))
                    ->create([
                        'user_id' => $user->id,
                    ]);
            }
        }

    }
}
