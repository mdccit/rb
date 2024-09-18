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
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business school without having permission'
                );
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
                ->first();
            if(!$business_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another business without having permission',
                    'You can not update business school without having permission'
                );
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
}
