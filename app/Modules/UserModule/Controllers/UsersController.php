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

    public function getPlayerProfile(Request $request,$user_slug)
    {
        try{
            $responseData = $this->userService->getPlayerProfile($user_slug);

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

    public function getCoachProfile(Request $request,$user_slug)
    {
        try{
            $responseData = $this->userService->getCoachProfile($user_slug);

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

    public function getBusinessManagerProfile(Request $request,$user_slug)
    {
        try{
            $responseData = $this->userService->getBusinessManagerProfile($user_slug);

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

    public function getParentProfile(Request $request,$user_slug)
    {
        try{
            $responseData = $this->userService->getParentProfile($user_slug);

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
