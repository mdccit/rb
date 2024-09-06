<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Modules\AuthModule\Services\ForgotPasswordService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    private $forgotPasswordService;

    function __construct()
    {
        //Init models
        $this->forgotPasswordService = new ForgotPasswordService();
    }

    public function forgotPasswordRequest(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
            ]);
            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }


            $user = User::connect(config('database.secondary'))->where('email', $request->email)->first();
            if ($user) {
                $password_reset = $this->forgotPasswordService->createPasswordResetRequest($user);
                $responseData = [
                    'password_reset_id' => $password_reset->id,
                ];

                return CommonResponse::getResponse(
                    200,
                    'Password reset request was recorded',
                    'Password reset request was recorded',
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

    public function passwordReset(Request $request,$password_reset_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'recovery_code' => 'required|numeric',
                'password' => 'required|string|min:6|confirmed',
            ]);
            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $password_reset = PasswordReset::connect(config('database.secondary'))
                ->where('id', $password_reset_id)
                ->where('is_used', false)->first();
            if ($password_reset) {
                $now = Carbon::now();
                if ($password_reset->expires_at >= $now) {
                    if($password_reset->recovery_code == $request->recovery_code){
                        $this->forgotPasswordService->resetUserPassword($password_reset,$request->password);

                        return CommonResponse::getResponse(
                            200,
                            'Successfully reset of user password',
                            'Successfully reset of your password'
                        );
                    } else {
                        return CommonResponse::getResponse(
                            422,
                            'OTP code is invalid',
                            'OTP code is invalid'
                        );
                    }
                } else {
                    return CommonResponse::getResponse(
                        422,
                        'Request was expired',
                        'Request was expired'
                    );
                }
            } else {
                return CommonResponse::getResponse(
                    422,
                    'Request was invalid',
                    'Request was invalid'
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
