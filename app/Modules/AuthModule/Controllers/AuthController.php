<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Extra\ThirdPartyAPI\GoogleAuthAPI;
use App\Http\Controllers\Controller;
use App\Models\BusinessManager;
use App\Models\Coach;
use App\Models\User;
use App\Modules\AuthModule\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $authService;

    function __construct()
    {
        //Init models
        $this->authService = new AuthService();
    }

    public function authRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:45',
                'last_name' => 'required|string|max:45',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $user = $this->authService->createUser($request->all(),false,$request->ip());
            $token = $user->createToken(config('app.name'))->accessToken;

            $responseData = [
                'token' => $token,
                'user_role' => $user->getUserRole->short_name,
                'user_id' => $user->id,
                'user_slug' => $user->slug,
                'user_name' => $user->display_name,

            ];

            return CommonResponse::getResponse(
                200,
                'Successfully Registered',
                'Successfully Registered',
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


    public function authLogin(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }


            $user = User::connect(config('database.default'))->where('email', $request->email)->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $this->authService->setLoggedUser($user->id,$request->ip());
                    $token = $user->createToken(config('app.name'))->accessToken;
                    $user_permission_type = 'none';
                    //Coach
                    if($user->getUserRole->id == config('app.user_roles.coach')) {
                        $coach = Coach::connect(config('database.secondary'))
                            ->where('user_id', $user->id)->first();
                        if($coach){
                            $user_permission_type = $coach->type;
                        }
                    }
                    //Business Manager
                    if($user->getUserRole->id == config('app.user_roles.business_manager')) {
                        $business_manager = BusinessManager::connect(config('database.secondary'))
                            ->where('user_id', $user->id)->first();
                        if($business_manager){
                            $user_permission_type = $business_manager->type;
                        }
                    }
                    $responseData = [
                        'token' => $token,
                        'user_role' => $user->getUserRole->short_name,
                        'user_permission_type' => $user_permission_type,
                        'user_id' => $user->id,
                        'user_slug' => $user->slug,
                        'user_name' => $user->display_name,
                    ];

                    return CommonResponse::getResponse(
                        200,
                        'User Login Successful',
                        'User Login Successful',
                        $responseData
                    );
                } else {
                    return CommonResponse::getResponse(
                        422,
                        'Invalid Password',
                        'Invalid Password'
                    );
                }
            } else {
                return CommonResponse::getResponse(
                    422,
                    'User does not exist',
                    'User does not exist'
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

    public function authLogout(Request $request)
    {
        try{
            $token = $request->user()->token();
            $token->revoke();

            return CommonResponse::getResponse(
                200,
                'User have been successfully logged out',
                'You have been successfully logged out'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function verifyEmail($user_id, Request $request) {
        try{
            if (!$request->hasValidSignature()) {
//                return CommonResponse::getResponse(
//                    401,
//                    'Invalid/Expired url provided.',
//                    'Invalid/Expired url provided'
//                );
                //TODO Must need to redirect verification failed page
                return redirect()->to(config('app.frontend_url').'verification-failed?userId='.$user_id.'&message=Your verification link was expired or invalid');
            }

            $this->authService->verifyUserAccount($user_id);

//            return CommonResponse::getResponse(
//                200,
//                'Successfully verified',
//                'Successfully verified'
//            );
            //TODO Must need to redirect verification success page
            return redirect()->to(config('app.frontend_url').'login?message=Your email address was successfully verified');
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function resendVerificationEmail($user_id, Request $request) {
        try{
            $user = $this->authService->resendVerificationEmail($user_id);

            if ($user->hasVerifiedEmail()) {
                return CommonResponse::getResponse(
                    422,
                    'Email already verified.',
                    'Email already verified.'
                );
            }

            return CommonResponse::getResponse(
                200,
                'Successfully resend the verification',
                'Successfully resend the verification, Please check your inbox'
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
