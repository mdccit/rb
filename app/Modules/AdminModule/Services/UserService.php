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
