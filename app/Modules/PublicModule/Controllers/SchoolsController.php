<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\PublicModule\Services\SchoolService;
use Illuminate\Http\Request;

class SchoolsController extends Controller
{
    private $schoolService;

    function __construct()
    {
        //Init models
        $this->schoolService = new SchoolService();
    }

    public function getSchoolProfile(Request $request,$school_slug)
    {
        try{
            $responseData = $this->schoolService->getSchoolProfile($school_slug);

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
