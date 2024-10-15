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

    public function updateSchoolUserManageType(Request $request,$user_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'school' => 'required|string',
                'user_permission_type' => 'required|string|in:viewer,editor',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->schoolUserService->updateSchoolUserManageType($request->all(),$user_id);

            return CommonResponse::getResponse(
                200,
                'Successfully updated',
                'Successfully updated'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function removeSchoolUser(Request $request,$user_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'school' => 'required|string',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->schoolUserService->removeSchoolUser($request->all(),$user_id);

            return CommonResponse::getResponse(
                200,
                'Successfully removed',
                'Successfully removed'
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
