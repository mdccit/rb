<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AuthModule\Services\UserService;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    private $userService;

    function __construct()
    {
        //Init models
        $this->userService = new UserService();
    }

    public function userDelete()
    {
        try{
            $user =User::connect(config('database.secondary'))->where('id',auth()->id())->first();

            if($user->user_role_id !=2){
            $this->userService->userDelete();
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully Account Deleted',
                    'Successfully Account Deleted',
                );

            }else{
                return CommonResponse::getResponse(
                    422,
                    'Admin Account Can Not Delete',
                    'Admin Account Can Not Delete'
                ); 
            }
            
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

}
