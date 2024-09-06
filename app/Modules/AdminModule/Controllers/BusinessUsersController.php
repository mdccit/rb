<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\BusinessUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessUsersController extends Controller
{
    private $businessUserService;

    function __construct()
    {
        //Init models
        $this->businessUserService = new BusinessUserService();
    }

    public function getAllBusinessUsers(Request $request,$business_id)
    {
        try{
            $responseData = $this->businessUserService->getAllBusinessUsers($business_id);

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

    public function searchUsers(Request $request,$business_id)
    {
        try{
            $dataSets = $this->businessUserService->searchUsers($request->all(),$business_id);

            $responseData = [
                'dataSets' => $dataSets,
            ];

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

    public function addBusinessUser(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'business' => 'required|string',
                'user' => 'required|string',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->businessUserService->addBusinessUser($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Added',
                'Successfully Added'
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
