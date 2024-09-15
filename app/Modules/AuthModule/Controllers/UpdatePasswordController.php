<?php

namespace App\Modules\AuthModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AuthModule\Services\UpdatePasswordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdatePasswordController extends Controller
{
    private $updatePasswordService;

    function __construct()
    {
        //Init models
        $this->updatePasswordService = new UpdatePasswordService();
    }

    public function updatePassword(Request $request)
    {
        try{
            
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string|current_password',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }
            
            $this->updatePasswordService->updatePassword($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully password Updated',
                'Successfully password Updated'
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
