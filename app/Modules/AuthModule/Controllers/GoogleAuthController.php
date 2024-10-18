<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Extra\ThirdPartyAPI\GoogleAuthAPI;
use App\Http\Controllers\Controller;
use App\Models\BusinessManager;
use App\Models\Coach;
use App\Modules\AuthModule\Services\AuthService;
use App\Traits\AzureBlobStorage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function Illuminate\Validation\message;

class GoogleAuthController extends Controller
{
    use AzureBlobStorage;

    private $googleAuthApi;
    private $authService;

    function __construct()
    {
        //Init google auth api
        $this->googleAuthApi = new GoogleAuthAPI();

        //Init models
        $this->authService = new AuthService();
    }

    public function getAuthUrl(Request $request)
    {
        try{
            $responseData = [
                'authUrl' => $this->googleAuthApi->getGoogleAuthUrl(),
            ];

            return CommonResponse::getResponse(
                200,
                'Successfully Generated',
                'Successfully Generated',
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

    public function authRegister(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'auth_code' => 'required|string',
            ]);
            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $authCode = $request->input('auth_code');
            $authCode = urldecode($authCode);

            $googleUser = $this->googleAuthApi->getGoogleUser($authCode);

            $user = User::connect(config('database.default'))
                ->where('email', $googleUser->offsetGet('email'))
                ->first();
            if (!$user) {
                $data = [
                    'first_name' => $googleUser->offsetGet('given_name'),
                    'last_name' => $googleUser->offsetGet('family_name'),
                    'email' => $googleUser->offsetGet('email'),
                    'password' => Str::random(8),
                    'provider_name' => 'google',
                    'provider_id' => $googleUser->id,
                    'google_access_token_json' => $this->googleAuthApi->getAccessToken(),
                ];

                $user = $this->authService->createUser($data,true,$request->ip());
                $token = $user->createToken(config('app.name'))->accessToken;

                $media_info = [
                    'profile_picture' => null,
                    'cover_picture' => null,
                ];

                $responseData = [
                    'token' => $token,
                    'user_role' => $user->getUserRole->short_name,
                    'user_id' => $user->id,
                    'user_slug' => $user->slug,
                    'user_name' => $user->display_name,
                    'media_info' => $media_info,
                    'user_type_id' => $user->user_type_id,
                ];

                return CommonResponse::getResponse(
                    200,
                    'Successfully Registered',
                    'Successfully Registered',
                    $responseData
                );
            } else {
                return CommonResponse::getResponse(
                    422,
                    'User already exist',
                    'User already exist'
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

    public function authLogin(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'auth_code' => 'required|string',
            ]);
            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $authCode = $request->input('auth_code');
            $authCode = urldecode($authCode);

            $googleUser = $this->googleAuthApi->getGoogleUser($authCode);

            $user = User::connect(config('database.default'))
                ->where('email', $googleUser->offsetGet('email'))
                ->where('provider_name', '=', 'google')
                ->where('provider_id', '=', $googleUser->id)
                ->first();
            if ($user) {
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

                $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
                $cover_picture = $this->getSingleFileByEntityId($user->id,'user_profile_cover');

                $media_info = [
                    'profile_picture' => $profile_picture,
                    'cover_picture' => $cover_picture,
                ];

                $responseData = [
                    'token' => $token,
                    'user_role' => $user->getUserRole->short_name,
                    'user_permission_type' => $user_permission_type,
                    'user_id' => $user->id,
                    'user_slug' => $user->slug,
                    'user_name' => $user->display_name,
                    'media_info' => $media_info,
                    'user_type_id' => $user->user_type_id,
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

}
