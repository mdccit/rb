<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Modules\AdminModule\Services\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessesController extends Controller
{
    private $businessService;

    function __construct()
    {
        //Init models
        $this->businessService = new BusinessService();
    }

    public function getAll(Request $request)
    {
        try{
            $dataSets = $this->businessService->getAllBusinesses($request->all());

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

    public function get(Request $request,$business_id)
    {
        try{
            $responseData = $this->businessService->getBusiness($business_id);

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

    public function registerBusiness(Request $request)
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

            $this->businessService->createBusiness($request->all());

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

    public function updateBusiness(Request $request,$business_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255|unique:businesses,name,' . $business_id,
                'bio' => 'nullable|string|max:5000',
                'is_verified' => 'required|boolean',
                'is_approved' => 'required|boolean',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->businessService->updateBusiness($request->all(),$business_id);

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

    public function destroyBusiness(Request $request,$business_id)
    {
        try{
            $this->businessService->deleteBusiness($business_id);

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

    public function viewBusiness(Request $request,$business_id)
    {
        try{
            $responseData = $this->businessService->viewBusiness($business_id);

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

    public function uploadProfilePicture(Request $request,$business_id)
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

            $business = Business::connect(config('database.secondary'))
                ->where('id', $business_id)
                ->first();
            if(!$business) {
                return CommonResponse::getResponse(
                    401,
                    'No business associated with this business id',
                    'No business associated with this business id'
                );
            }

            $responseData = $this->businessService->uploadProfilePicture($request->file('file'),$business_id);

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

    public function uploadCoverPicture(Request $request,$business_id)
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

            $business = Business::connect(config('database.secondary'))
                ->where('id', $business_id)
                ->first();
            if(!$business) {
                return CommonResponse::getResponse(
                    401,
                    'No business associated with this business id',
                    'No business associated with this business id'
                );
            }

            $responseData = $this->businessService->uploadCoverPicture($request->file('file'),$business_id);

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

    public function uploadMedia(Request $request,$business_id)
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

            $business = Business::connect(config('database.secondary'))
                ->where('id', $business_id)
                ->first();
            if(!$business) {
                return CommonResponse::getResponse(
                    401,
                    'No business associated with this business id',
                    'No business associated with this business id'
                );
            }

            $responseData = $this->businessService->uploadMedia($request->file('files'),$business_id);

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
            $this->businessService->removeMedia($media_id);

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
