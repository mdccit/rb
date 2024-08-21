<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AuthModule\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userService;

    function __construct()
    {
        //Init models
        $this->userService = new UserService();
    }

    public function userDelete(Request $request)
    {
        try{
           
            
            $this->userService->userDelete($request->all());
            
            return CommonResponse::getResponse(
                200,
                'Successfully Deleted',
                'Successfully Deleted',
                );

        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

}
