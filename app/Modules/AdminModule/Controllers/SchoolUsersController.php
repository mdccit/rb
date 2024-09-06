<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\SchoolUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolUsersController extends Controller
{
    private $schoolUserService;

    function __construct()
    {
        //Init models
        $this->schoolUserService = new SchoolUserService();
    }

    public function getAllSchoolUsers(Request $request,$school_id)
    {
        try{
            $responseData = $this->schoolUserService->getAllSchoolUsers($school_id);

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

    public function searchUsers(Request $request,$school_id)
    {
        try{
            $dataSets = $this->schoolUserService->searchUsers($request->all(),$school_id);

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

    public function addSchoolUser(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'school' => 'required|string',
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

            $this->schoolUserService->addSchoolUser($request->all());

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
