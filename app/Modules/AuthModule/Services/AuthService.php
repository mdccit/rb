<?php


namespace App\Modules\AuthModule\Services;


use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use hisorange\BrowserDetect;
use hisorange\BrowserDetect\Parser as Browser;
use App\Models\ModerationRequest;

class AuthService
{
    public function createUser(array $data, $is_google_auth = false,$ip_address){
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
                'remember_token' => Str::random(10),
                'last_logged_at' => Carbon::now(),
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
                'remember_token' => Str::random(10),
                'last_logged_at' => Carbon::now(),
            ]);
        }

         // Create moderation reques
         ModerationRequest::create([
            'moderatable_type' => User::class,
            'moderatable_id' => $user->id,
            'priority' => 'medium',
            'created_by' => $user->id,
            'notes' => 'User signup requires approval*',
        ]);

        $description = Browser::browserName() . ' on ' .Browser::platformName() . ' (' . Browser::deviceType() . ' Device)';
        UserSession::connect(config('database.default'))
            ->create([
                'user_id' => $user->id,
                'sign_in_at' => Carbon::now(),
                'ip_address' => $ip_address,
                'description' => $description,
            ]);

        $user->sendEmailVerificationNotification();

        return $user;

    }

    public function setLoggedUser($user_id,$ip_address){
        User::connect(config('database.default'))
            ->where('id', $user_id)
            ->update([
                'last_logged_at' => Carbon::now(),
            ]);
        $description = Browser::browserName() . ' on ' .Browser::platformName() . ' (' . Browser::deviceType() . ' Device)';
        UserSession::connect(config('database.default'))
            ->create([
                'user_id' => $user_id,
                'sign_in_at' => Carbon::now(),
                'ip_address' => $ip_address,
                'description' => $description,
            ]);
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
