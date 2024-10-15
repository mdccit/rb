<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AuthModule\Services\AuthService;
use App\Modules\AuthModule\Services\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    private $registerService;

    function __construct()
    {
        //Init models
        $this->registerService = new RegisterService();
    }

    public function playerRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'country' => 'required|numeric',
                'phone_code_country' => 'required|numeric',
                'phone_number' => 'required|string|max:15',
                'gender' => 'required|string|in:male,female,other',
                'handedness' => 'required|string|in:right,left,both',
                'height_in_cm' => 'boolean',
                'height_cm' => 'nullable|numeric',
                'height_ft' => 'nullable|numeric',
                'height_in' => 'nullable|numeric',
                'player_budget' => 'required|numeric',
                'utr' => 'required|numeric',
                'gpa' => 'required|numeric',
                'graduation_month_year' => 'required|date',
                'nationality' => 'required|numeric',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = $request->user();
            $this->registerService->createPlayer($request->all(),$user);

            return CommonResponse::getResponse(
                200,
                'Your account has been successfully updated',
                'Your account has been successfully updated'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function coachRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'country' => 'required|numeric',
                'phone_code_country' => 'required|numeric',
                'phone_number' => 'required|string|max:15',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = $request->user();
            $this->registerService->createCoach($request->all(),$user);

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

    public function businessManagerRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'country' => 'required|numeric',
                'phone_code_country' => 'required|numeric',
                'phone_number' => 'required|string|max:15',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = $request->user();
            $this->registerService->createBusinessManager($request->all(),$user);

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

    public function parentRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'country' => 'required|numeric',
                'phone_code_country' => 'required|numeric',
                'phone_number' => 'required|string|max:15',

                'player_first_name' => 'required|string|max:45',
                'player_last_name' => 'required|string|max:45',
                'email' => 'required|string|email|max:255|unique:users',
                'player_country' => 'required|numeric',
                'player_phone_code_country' => 'required|numeric',
                'player_phone_number' => 'required|string|max:15',
                'player_gender' => 'required|string|in:male,female,other',
                'player_handedness' => 'required|string|in:right,left,both',
                'player_height_in_cm' => 'boolean',
                'player_height_cm' => 'nullable|numeric',
                'player_height_ft' => 'nullable|numeric',
                'player_height_in' => 'nullable|numeric',
                'player_budget' => 'required|numeric',
                'player_utr' => 'required|numeric',
                'player_gpa' => 'required|numeric',
                'player_graduation_month_year' => 'required|date',
                'player_nationality' => 'required|numeric',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = $request->user();
            $this->registerService->createParent($request->all(),$user);

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


    public function coachRegisterStep3(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'user_type' => 'required|in:2,3', // 2: Standard, 3: Premium
            'subscription_type' => 'required|in:trial,paid', // For selecting subscription type
        ]);

        if ($validator->fails()) {
            return CommonResponse::getResponse(
                422,
                $validator->errors(),
                'Input validation failed'
            );
        }

        $user = $request->user();

        // Check if the user is a coach
        if (!$user->isCoach()) {
            return CommonResponse::getResponse(
                403,
                'User is not authorized as a coach',
                'Unauthorized access'
            );
        }

        // If the user selects Premium (user_type = 3), set up subscription
        if ($request->input('user_type') == 3) {
            // Create a new subscription (trial or paid) for the user
            $subscription = $this->registerService->createSubscription($request->all(), $user);

            // Assign premium user type to the user
            $user->user_type_id = 3; // Premium user type
            $user->save();
        }

        return CommonResponse::getResponse(
            200,
            'Successfully registered for premium account',
            'Step 3 completed'
        );
    } catch (\Exception $e) {
        return CommonResponse::getResponse(
            422,
            $e->getMessage(),
            'Something went wrong'
        );
    }
}

}
