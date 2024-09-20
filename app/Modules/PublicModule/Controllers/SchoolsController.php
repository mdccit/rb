<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolUser;
use App\Modules\PublicModule\Services\SchoolService;
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

    public function updateBasicInfo(Request $request,$school_slug)
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

            $school_user = SchoolUser::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'school_users.school_id')
                ->where('schools.slug', $school_slug)
                ->where('school_users.user_id', auth()->id())
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update another school without having permission'
                );
            }

            $this->schoolService->updateBasicInfo($request->all(),$school_slug);

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

    public function updateBio(Request $request,$school_slug)
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

            $school_user = SchoolUser::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'school_users.school_id')
                ->where('schools.slug', $school_slug)
                ->where('school_users.user_id', auth()->id())
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update another school without having permission'
                );
            }

            $this->schoolService->updateBio($request->all(),$school_slug);

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

    public function addNewAcademic(Request $request,$school_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'academic' => 'required|string|max:45',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school_user = SchoolUser::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'school_users.school_id')
                ->where('schools.slug', $school_slug)
                ->where('school_users.user_id', auth()->id())
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update another school without having permission'
                );
            }

            $this->schoolService->addNewAcademic($request->all(),$school_slug);

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

    public function removeAcademic(Request $request,$school_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'academic' => 'required|string|max:45',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school_user = SchoolUser::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'school_users.school_id')
                ->where('schools.slug', $school_slug)
                ->where('school_users.user_id', auth()->id())
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update another school without having permission'
                );
            }

            $this->schoolService->removeAcademic($request->all(),$school_slug);

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

    public function updateTennisInfo(Request $request,$school_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'conference' => 'required|numeric',
                'division' => 'required|numeric',
                'average_utr' => 'required|numeric|between:0,99.99',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school_user = SchoolUser::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'school_users.school_id')
                ->where('schools.slug', $school_slug)
                ->where('school_users.user_id', auth()->id())
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update another school without having permission'
                );
            }

            $this->schoolService->updateTennisInfo($request->all(),$school_slug);

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

    public function updateStatusInfo(Request $request,$school_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'tuition_in_of_state' => 'required|numeric',
                'tuition_out_of_state' => 'required|numeric',
                'cost_of_attendance' => 'required|numeric',
                'graduation_rate' => 'required|numeric|between:0,99.99',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school_user = SchoolUser::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'school_users.school_id')
                ->where('schools.slug', $school_slug)
                ->where('school_users.user_id', auth()->id())
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update another school without having permission'
                );
            }

            $this->schoolService->updateStatusInfo($request->all(),$school_slug);

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
