<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Extra\ThirdPartyAPI\GoogleAuthAPI;
use App\Http\Controllers\Controller;
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
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $user = $this->authService->createUser($request->all());
            $token = $user->createToken(config('app.name'))->accessToken;

            $responseData = [
                'token' => $token,
                'user_role' => $user->getUserRole->short_name,
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
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }


            $user = User::connect(config('database.default'))->where('email', $request->email)->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken(config('app.name'))->accessToken;
                    $responseData = [
                        'token' => $token,
                        'user_role' => $user->getUserRole->short_name,
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
}
