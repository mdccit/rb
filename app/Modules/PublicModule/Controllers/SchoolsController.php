<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolUser;
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
    
    public function destroy($user_team_id){
        try{
           
            $existing = SchoolUser::connect(config('database.secondary'))
                            ->where('id',$user_team_id)
                            ->exists();
            
            if($existing){

                $this->schoolService->destroy($user_team_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully School User Deleted',
                    'Successfully School User Deleted'           
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'This School User is not existing',
                    'This School User is not existing'
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
