<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Modules\AdminModule\Services\SchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolsController extends Controller
{
    private $schoolService;

    function __construct()
    {
        //Init models
        $this->schoolService = new SchoolService();
    }

    public function getAll(Request $request)
    {
        try{
            $dataSets = $this->schoolService->getAllSchools($request->all());

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

    public function get(Request $request,$school_id)
    {
        try{
            $responseData = $this->schoolService->getSchool($school_id);

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

    public function registerSchool(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->schoolService->createSchool($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Registered',
                'Successfully Registered'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function updateSchool(Request $request,$school_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255|unique:schools,name,' . $school_id,
                'bio' => 'nullable|string|max:5000',
                'is_verified' => 'required|boolean',
                'is_approved' => 'required|boolean',
                'conference' => 'nullable|numeric',
                'division' => 'nullable|numeric',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->schoolService->updateSchool($request->all(),$school_id);

            return CommonResponse::getResponse(
                200,
                'Successfully Updated',
                'Successfully Updated'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function destroySchool(Request $request,$school_id)
    {
        try{
            $this->schoolService->deleteSchool($school_id);

            return CommonResponse::getResponse(
                200,
                'Successfully deleted',
                'Successfully deleted'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function viewSchool(Request $request,$school_id)
    {
        try{
            $responseData = $this->schoolService->viewSchool($school_id);

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

    public function uploadProfilePicture(Request $request,$school_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'file.*' => 'required|mimes:jpg,jpeg,png|max:51200',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school = School::connect(config('database.secondary'))
                ->where('id', $school_id)
                ->first();
            if(!$school) {
                return CommonResponse::getResponse(
                    401,
                    'No school associated with this school id',
                    'No school associated with this school id'
                );
            }

            $responseData = $this->schoolService->uploadProfilePicture($request->file('file'),$school_id);

            return CommonResponse::getResponse(
                200,
                'Successfully Uploaded',
                'Successfully Uploaded',
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

    public function uploadCoverPicture(Request $request,$school_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'file.*' => 'required|mimes:jpg,jpeg,png|max:51200',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school = School::connect(config('database.secondary'))
                ->where('id', $school_id)
                ->first();
            if(!$school) {
                return CommonResponse::getResponse(
                    401,
                    'No school associated with this school id',
                    'No school associated with this school id'
                );
            }

            $responseData = $this->schoolService->uploadCoverPicture($request->file('file'),$school_id);

            return CommonResponse::getResponse(
                200,
                'Successfully Uploaded',
                'Successfully Uploaded',
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

    public function uploadMedia(Request $request,$school_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'files.*' => 'required|mimes:jpg,jpeg,png,mp4|max:51200',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school = School::connect(config('database.secondary'))
                ->where('id', $school_id)
                ->first();
            if(!$school) {
                return CommonResponse::getResponse(
                    401,
                    'No school associated with this school id',
                    'No school associated with this school id'
                );
            }

            $responseData = $this->schoolService->uploadMedia($request->file('files'),$school_id);

            return CommonResponse::getResponse(
                200,
                'Successfully Uploaded',
                'Successfully Uploaded',
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

    public function removeMedia($media_id)
    {
        try{
            $this->schoolService->removeMedia($media_id);

            return CommonResponse::getResponse(
                200,
                'Successfully Removed Media',
                'Successfully Removed Media',
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
