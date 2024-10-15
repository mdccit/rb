<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\BusinessManager;
use App\Modules\PublicModule\Services\BusinessService;
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

    public function getBusinessProfile(Request $request,$business_slug)
    {
        try{
            $responseData = $this->businessService->getBusinessProfile($business_slug);

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

    public function updateBasicInfo(Request $request,$business_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:45',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $business_user = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('businesses.slug', $business_slug)
                ->where('business_managers.user_id', auth()->id())
                ->select(
                    'business_managers.type as user_permission_type',
                )
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business without having permission'
                );
            }else{
                if($business_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this business information',
                        'You have not permission to edit this business information'
                    );
                }
            }

            $this->businessService->updateBasicInfo($request->all(),$business_slug);

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

    public function updateBio(Request $request,$business_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'bio' => 'required|string|min:3|max:5000',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $business_user = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('businesses.slug', $business_slug)
                ->where('business_managers.user_id', auth()->id())
                ->select(
                    'business_managers.type as user_permission_type',
                )
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business without having permission'
                );
            }else{
                if($business_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this business information',
                        'You have not permission to edit this business information'
                    );
                }
            }

            $this->businessService->updateBio($request->all(),$business_slug);

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

    public function uploadProfilePicture(Request $request,$business_slug)
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

            $business_user = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('businesses.slug', $business_slug)
                ->where('business_managers.user_id', auth()->id())
                ->select(
                    'business_managers.type as user_permission_type',
                )
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business without having permission'
                );
            }else{
                if($business_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this business information',
                        'You have not permission to edit this business information'
                    );
                }
            }

            $responseData = $this->businessService->uploadProfilePicture($request->file('file'),$business_slug);

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

    public function uploadCoverPicture(Request $request,$business_slug)
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

            $business_user = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('businesses.slug', $business_slug)
                ->where('business_managers.user_id', auth()->id())
                ->select(
                    'business_managers.type as user_permission_type',
                )
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business without having permission'
                );
            }else{
                if($business_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this business information',
                        'You have not permission to edit this business information'
                    );
                }
            }

            $responseData = $this->businessService->uploadCoverPicture($request->file('file'),$business_slug);

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

    public function uploadMedia(Request $request,$business_slug)
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

            $business_user = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('businesses.slug', $business_slug)
                ->where('business_managers.user_id', auth()->id())
                ->select(
                    'business_managers.type as user_permission_type',
                )
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business without having permission'
                );
            }else{
                if($business_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this business information',
                        'You have not permission to edit this business information'
                    );
                }
            }

            $responseData = $this->businessService->uploadMedia($request->file('files'),$business_slug);

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

    public function removeMedia(Request $request,$business_slug)
    {
        try{
            $business_user = BusinessManager::connect(config('database.secondary'))
                ->join('businesses', 'businesses.id', '=' ,'business_managers.business_id')
                ->where('businesses.slug', $business_slug)
                ->where('business_managers.user_id', auth()->id())
                ->select(
                    'business_managers.type as user_permission_type',
                )
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business without having permission'
                );
            }else{
                if($business_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this business information',
                        'You have not permission to edit this business information'
                    );
                }
            }

            $this->businessService->removeMedia($request->all(),$business_slug);

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
