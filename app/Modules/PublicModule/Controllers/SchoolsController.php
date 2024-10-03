<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
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

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
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

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
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
                'academic' => 'required|string|min:3|max:25',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
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
                'academic' => 'required|string|min:3|max:25',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
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

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
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

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
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

    public function uploadProfilePicture(Request $request,$school_slug)
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

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
            }

            $responseData = $this->schoolService->uploadProfilePicture($request->file('file'),$school_slug);

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

    public function uploadCoverPicture(Request $request,$school_slug)
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

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
            }

            $responseData = $this->schoolService->uploadCoverPicture($request->file('file'),$school_slug);

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

    public function uploadMedia(Request $request,$school_slug)
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

            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
            }

            $responseData = $this->schoolService->uploadMedia($request->file('files'),$school_slug);

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

    public function removeMedia(Request $request,$school_slug)
    {
        try{
            $school_user = Coach::connect(config('database.secondary'))
                ->join('schools', 'schools.id', '=' ,'coaches.school_id')
                ->where('schools.slug', $school_slug)
                ->where('coaches.user_id', auth()->id())
                ->select(
                    'coaches.type as user_permission_type',
                )
                ->first();
            if(!$school_user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another school without having permission',
                    'You can not update school without having permission'
                );
            }else{
                if($school_user->user_permission_type != config('app.user_permission_type.editor')){
                    return CommonResponse::getResponse(
                        401,
                        'You have not permission to edit this school information',
                        'You have not permission to edit this school information'
                    );
                }
            }

            $this->schoolService->removeMedia($request->all(),$school_slug);

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
