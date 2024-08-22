<?php


namespace App\Modules\AuthModule\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserService
{
    public function userDelete(){
        
        User::connect(config('database.default'))->destroy(auth()->id());
        $user = Auth::user();
        $user->tokens->each->delete();
    }
    
}
