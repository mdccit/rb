<?php


namespace App\Modules\AuthModule\Services;


use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function createUser(array $data, $is_google_auth = false){
        if($is_google_auth){
            $user = User::connect(config('database.default'))
                ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'display_name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'user_role_id' => config('app.user_roles.default'),
                'user_type_id' => config('app.user_types.free'),
                'password' => Hash::make($data['password']),
                'provider_name' => $data['provider_name'],
                'provider_id' => $data['provider_id'],
                'google_access_token_json' => $data['google_access_token_json'],
                'remember_token' => Str::random(10)
            ]);
        }else{
            $user = User::connect(config('database.default'))
                ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'display_name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'user_role_id' => config('app.user_roles.default'),
                'user_type_id' => config('app.user_types.free'),
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(10)
            ]);
        }

        $user->sendEmailVerificationNotification();

        return $user;

    }

    public function verifyUserAccount($user_id){
        $user = User::connect(config('database.default'))->findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    }

    public function resendVerificationEmail($user_id){
        $user = User::connect(config('database.default'))->findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return $user;
    }
}
