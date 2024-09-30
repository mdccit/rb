<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\PublicModule\Services\PlayerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayersController extends Controller
{
    private $playerService;

    function __construct()
    {
        //Init models
        $this->playerService = new PlayerService();
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

            $this->playerService->updateBasicInfo($request->all(),$user_slug);

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

            $this->playerService->updateBio($request->all(),$user_slug);

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

            $responseData = $this->playerService->uploadProfilePicture($request->file('file'),$user_slug);

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

            $responseData = $this->playerService->uploadCoverPicture($request->file('file'),$user_slug);

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

            $responseData = $this->playerService->uploadMedia($request->file('files'),$user_slug);

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
            $this->playerService->removeMedia($media_id);

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

            $this->playerService->updateContactInfo($request->all(),$user_slug);

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
                'nationality' => 'required|numeric',
                'gender' => 'required|string|in:male,female,other',
                'date_of_birth' => 'required|date',

                'handedness' => 'required|string|in:left,right,both',
                'preferred_surface' => 'required|string|in:hard,clay,grass,artificial',

                'weight_in_kg' => 'required|boolean',
                'weight_kg' => 'nullable|required_if:weight_in_kg,true|numeric',
                'weight_lb' => 'nullable|required_if:weight_in_kg,false|numeric',

                'height_in_cm' => 'required|boolean',
                'height_cm' => 'nullable|required_if:height_in_cm,true|numeric',
                'height_ft' => 'nullable|required_if:height_in_cm,false|numeric',
                'height_in' => 'nullable|required_if:height_in_cm,false|numeric',

                'graduation_month_year' => 'required|date',
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

            $this->playerService->updatePersonalOtherInfo($request->all(),$user_slug);

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

    public function updateBudget(Request $request,$user_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'budget_max' => 'required|numeric',
                'budget_min' => 'required|numeric',
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

            $this->playerService->updateBudget($request->all(),$user_slug);

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

    public function updateCoreValues(Request $request,$user_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'gpa' => 'required|numeric|between:0,99.99',
                'utr' => 'required|numeric|between:0,99.99',
                'sat_score' => 'required|numeric|between:0,99.99',
                'act_score' => 'required|numeric|between:0,99.99',
                'toefl_score' => 'required|numeric|between:0,99.99',
                'atp_ranking' => 'required|numeric|between:0,99.99',
                'itf_ranking' => 'required|numeric|between:0,99.99',
                'national_ranking' => 'required|numeric|between:0,99.99',
                'wtn_score_manual' => 'required|numeric|between:0,99.99',
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

            $this->playerService->updateCoreValues($request->all(),$user_slug);

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
