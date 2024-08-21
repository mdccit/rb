<?php


namespace App\Modules\AuthModule\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserService
{
    public function userDelete(){
        
        $user =User::connect(config('database.default'))->where('id',auth()->id())->first();

        if($user->user_role_id !=2){

            User::connect(config('database.default'))->destroy(auth()->id());
            $user = Auth::user();
            $user->tokens->each->delete();
        }
        
        

    
    }
    
}
