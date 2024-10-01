<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\PublicModule\Services\ParentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParentsController extends Controller
{
    private $parentService;

    function __construct()
    {
        //Init models
        $this->parentService = new ParentService();
    }

    public function updateBasicInfo(Request $request,$user_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:45',
                'last_name' => 'required|string|max:45',
                'other_names' => 'nullable|string|max:255',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = User::connect(config('database.secondary'))
                ->where('slug', $user_slug)
                ->where('id', auth()->id())
                ->first();
            if(!$user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another user',
                    'You can not update another user'
                );
            }

            $this->parentService->updateBasicInfo($request->all(),$user_slug);

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

    public function updateBio(Request $request,$user_slug)
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

            $user = User::connect(config('database.secondary'))
                ->where('slug', $user_slug)
                ->where('id', auth()->id())
                ->first();
            if(!$user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another user',
                    'You can not update another user'
                );
            }

            $this->parentService->updateBio($request->all(),$user_slug);

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

    public function updateContactInfo(Request $request,$user_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'address_line_1' => 'required|string|max:80',
                'address_line_2' => 'nullable|string|max:80',
                'city' => 'nullable|required_with:address_line_1|string|max:48',
                'state_province' => 'nullable|required_with:address_line_1|string|max:48',
                'postal_code' => 'nullable|required_with:address_line_1|string|max:24',

                'country' => 'required|numeric',

                'phone_number' => 'required|string|max:15',
                'phone_code_country' => 'required|numeric',

                'email' => 'required|unique:users,email,'.auth()->id(),
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = User::connect(config('database.secondary'))
                ->where('slug', $user_slug)
                ->where('id', auth()->id())
                ->first();
            if(!$user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another user',
                    'You can not update another user'
                );
            }

            $this->parentService->updateContactInfo($request->all(),$user_slug);

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

    public function updatePersonalOtherInfo(Request $request,$user_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'nationality' => 'nullable|numeric',
                'gender' => 'required|string|in:male,female,other',
                'date_of_birth' => 'nullable|date',
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = User::connect(config('database.secondary'))
                ->where('slug', $user_slug)
                ->where('id', auth()->id())
                ->first();
            if(!$user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another user',
                    'You can not update another user'
                );
            }

            $this->parentService->updatePersonalOtherInfo($request->all(),$user_slug);

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

    public function uploadProfilePicture(Request $request,$user_slug)
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

            $user = User::connect(config('database.secondary'))
                ->where('slug', $user_slug)
                ->where('id', auth()->id())
                ->first();
            if(!$user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another user',
                    'You can not update another user'
                );
            }

            $responseData = $this->parentService->uploadProfilePicture($request->file('file'),$user_slug);

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

    public function uploadCoverPicture(Request $request,$user_slug)
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

            $user = User::connect(config('database.secondary'))
                ->where('slug', $user_slug)
                ->where('id', auth()->id())
                ->first();
            if(!$user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another user',
                    'You can not update another user'
                );
            }

            $responseData = $this->parentService->uploadCoverPicture($request->file('file'),$user_slug);

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

    public function uploadMedia(Request $request,$user_slug)
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

            $user = User::connect(config('database.secondary'))
                ->where('slug', $user_slug)
                ->where('id', auth()->id())
                ->first();
            if(!$user) {
                return CommonResponse::getResponse(
                    401,
                    'You can not update another user',
                    'You can not update another user'
                );
            }

            $responseData = $this->parentService->uploadMedia($request->file('files'),$user_slug);

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
            $this->parentService->removeMedia($media_id);

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
