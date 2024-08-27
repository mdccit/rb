<?php

namespace App\Modules\UserModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\UserModule\Services\UserService;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    private $userService;

    function __construct()
    {
        //Init models
        $this->userService = new UserService();
    }

    public function getPlayerProfile(Request $request,$user_id)
    {
        try{
            $responseData = $this->userService->getPlayerProfiles($user_id);

            return CommonResponse::getResponse(
                200,
                'Successfully fetched',
                'Successfully fetched',
                $responseData
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
