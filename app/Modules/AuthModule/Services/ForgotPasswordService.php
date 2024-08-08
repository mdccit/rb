<?php


namespace App\Modules\AuthModule\Services;


use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ForgotPasswordEmailNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ForgotPasswordService
{
    public function createPasswordResetRequest($user){
        $password_reset = PasswordReset::connect(config('database.secondary'))
            ->where('user_id', $user->id)
            ->where('is_used', false)->first();
        if($password_reset){
            PasswordReset::connect(config('database.default'))
                ->where('id', $password_reset->id)
                ->update([
                    'recovery_code' => random_int(100000, 999999),
                    'expires_at' => Carbon::now()->addMinutes(10),
                ]);
        }else{
            $password_reset = PasswordReset::connect(config('database.default'))
                ->create([
                    'user_id' => $user->id,
                    'recovery_code' => random_int(100000, 999999),
                    'expires_at' => Carbon::now()->addMinutes(10),
                ]);
        }

        Notification::route('mail',$user->email)->notify(new ForgotPasswordEmailNotification($user,$password_reset));

        return $password_reset;
    }

    public function resetUserPassword($password_reset,$password){
        PasswordReset::connect(config('database.default'))
            ->where('id', $password_reset->id)
            ->update([
                'is_used' => true
            ]);

        User::connect(config('database.default'))
            ->where('id', $password_reset->user_id)
            ->update([
                'password' => Hash::make($password)
            ]);
    }
}
