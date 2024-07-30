<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Extra\ThirdPartyAPI\GoogleAuthAPI;
use App\Http\Controllers\Controller;
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
    private $googleAuthApi;

    function __construct()
    {
        //Init google auth api
        $this->googleAuthApi = new GoogleAuthAPI();
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
                    $validator->errors()->all(),
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

                $user = $this->createUser($data);
                $token = $user->createToken(config('app.name'))->accessToken;

                $responseData = [
                    'token' => $token
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

    private function createUser(array $data){
        return User::connect(config('database.default'))->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'display_name' => $data['first_name'].' '.$data['last_name'],
            'email' => $data['email'],
            'user_role_id' => config('app.user_roles.default'),
            'user_type_id' => config('app.user_types.free'),
            'password' => Hash::make($data['password']),
            'provider_name' => $data['provider_name'],
            'provider_id' => $data['provider_id'],
            'google_access_token_json' => $data['google_access_token_json'],
            'remember_token' => Str::random(10)
        ]);
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
                    $validator->errors()->all(),
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
                $token = $user->createToken(config('app.name'))->accessToken;
                $responseData = [
                    'token' => $token,
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
