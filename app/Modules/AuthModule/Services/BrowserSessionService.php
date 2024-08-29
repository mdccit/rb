<?php


namespace App\Modules\AuthModule\Services;


use Illuminate\Support\Facades\Auth;

class BrowserSessionService
{
    
    public function logOutOtherBrowserSession($request){
        
        $user = Auth::user();
        $tokens = $user->tokens;
        $current_token = $request->user()->token();
    
        foreach($tokens as $token){
            if($token->id !=$current_token->id){
                $token->revoke();
            }
        }
    
    }

   
}
