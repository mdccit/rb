<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UsersController extends Controller
{
    private $userService;

    function __construct()
    {
        //Init models
        $this->userService = new UserService();
    }

    public function getAll(Request $request)
    {
        try{
            $dataSets = $this->userService->getAllUsers($request->all());

            $responseData = [
                'dataSets' => $dataSets,
            ];

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

    public function get(Request $request,$user_id)
    {
        try{
            $responseData = $this->userService->getUser($user_id);

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

    public function registerUser(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:45',
                'last_name' => 'required|string|max:45',
                'email' => 'required|string|email|max:255|unique:users',
                'is_set_email_verified' => 'required|boolean',
                'password' => 'required|string|min:6',
                'user_role' => 'required|numeric',
                'phone_code_country' => 'required|numeric',
                'phone_number' => 'required|string|max:15|unique:user_phones',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $this->userService->createUser($request->all());

            return CommonResponse::getResponse(
                200,
                'Successfully Registered',
                'Successfully Registered'
            );
        }catch (\Exception $e){
            return CommonResponse::getResponse(
                422,
                $e->getMessage(),
                'Something went to wrong'
            );
        }
    }

    public function updateUser(Request $request,$user_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:45',
                'last_name' => 'required|string|max:45',
                'other_names' => 'nullable|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user_id,
                'is_set_email_verified' => 'required|boolean',
                'is_approved' => 'required|boolean',
                'password' => 'nullable|string|min:6',
                'user_role' => 'required|numeric',
                'phone_code_country' => 'required|numeric',
                'phone_number' => 'required|string|max:15',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $this->userService->updateUser($request->all(),$user_id);

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

    public function userAccountDelete($user_id){
        try{
            $user =User::connect(config('database.secondary'))->where('id',$user_id)->first();

            if($user->user_role_id !=2){

                $this->userService->userDelete($user_id);
                
                return CommonResponse::getResponse(
                    200,
                    'Successfully Account Deleted',
                    'Successfully Account Deleted',
                );

            }else{
                return CommonResponse::getResponse(
                    422,
                    'Admin Account Can Not Delete',
                    'Admin Account Can Not Delete'
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
