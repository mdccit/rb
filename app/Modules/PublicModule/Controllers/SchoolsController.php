<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\PublicModule\Services\SchoolService;
use Illuminate\Http\Request;
use App\Models\SchoolUser;

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

    public function destroy($id){
        try{
           
            $existing = SchoolUser::connect(config('database.secondary'))
                            ->where('id',$id)
                            ->exists();
            
            if($existing){

                $this->schoolService->destroy($id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully School User Deleted',
                    'Successfully School Use Deleted'           
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'This school user is not existing',
                    'This school user is not existing'
                );
            }
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }
}
