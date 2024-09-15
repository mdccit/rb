<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\PublicModule\Services\BusinessService;
use Illuminate\Http\Request;

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
}
