<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\TransferPlayerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TransferPlayer;
use Illuminate\Validation\Rule;

class TransferPlayerController extends Controller
{
    private $transferPlayerService;

    function __construct()
    {
        //Init models
        $this->transferPlayerService = new TransferPlayerService();
    }

    public function getAllUsers(Request $request)
    {
        try{
            $dataSets = $this->transferPlayerService->getAllUsers($request->all());

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

    
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:75',
                'school' => 'required|string',
                'email' => 'required|string|email|max:255|unique:transfer_players',
                'utr_score_manual' => 'required|numeric',
                'year' => 'required|string|in:freshman,sophomore,junior,senior',
                'win' => 'required|numeric',
                'loss' => 'required|numeric',
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:4096',
                'handedness' => 'required|string|in:right,left,both',
                'phone_code_country' => 'required|numeric',
                'phone_number' => 'required|string|max:15|unique:transfer_players',
                'height_in_cm' => 'boolean',
                'height_cm' => 'nullable|numeric',
                'height_ft' => 'nullable|numeric',
                'height_in' => 'nullable|numeric',
                'gender' => 'required|string|in:male,female,other',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }
            
            if ($image =$request->file('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/Transfer_user_profile_photo', $filename);
                $request['profile_photo_path'] ='Transfer_user_profile_photo/' . $filename;
            }

            $this->transferPlayerService->store($request->all());

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

    public function update(Request $request,$transfer_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:75',
                'school' => 'required|string',
                'email' => ['required', 'email', 'max:255', Rule::unique('transfer_players')->ignore($transfer_id)],
                'utr_score_manual' => 'required|numeric',
                'year' => 'required|string|in:freshman,sophomore,junior,senior',
                'win' => 'required|numeric',
                'loss' => 'required|numeric',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
                'handedness' => 'required|string|in:right,left,both',
                'phone_code_country' => 'required|numeric',
                'phone_number' => ['required','string','max:15',Rule::unique('transfer_players')->ignore($transfer_id)],
                'height_in_cm' => 'boolean',
                'height_cm' => 'nullable|numeric',
                'height_ft' => 'nullable|numeric',
                'height_in' => 'nullable|numeric',
                'gender' => 'required|string|in:male,female,other',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }
            $transfer_player = TransferPlayer::connect(config('database.secondary'))->where('id', $transfer_id)->first();
             
            if($transfer_player){

                if ($image =$request->file('profile_photo')) {
                    $file = $request->file('profile_photo');
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/Transfer_user_profile_photo', $filename);
                    $request['profile_photo_path'] ='Transfer_user_profile_photo/' . $filename;
                }else{
                    $request['profile_photo_path'] =$transfer_player->profile_photo_path;

                }
    
                $this->transferPlayerService->update($request->all(),$transfer_id);
    
                return CommonResponse::getResponse(
                    200,
                    'Successfully Updated',
                    'Successfully Updated'
                );
            }else{

                return CommonResponse::getResponse(
                    422,
                    'Transfer player does not exist',
                    'Transfer player does not exist'
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

    public function destory($transfer_id){
        try{
           
            $transfer_player = TransferPlayer::connect(config('database.secondary'))->where('id', $transfer_id)->first();

            if($transfer_player){

                $this->transferPlayerService->destroy($transfer_id);

                return CommonResponse::getResponse(
                        200,
                        'Successfully Resource Deleted',
                        'Successfully Resource Deleted'
                    );
            }else{

                return CommonResponse::getResponse(
                    422,
                    'Resource does not exist',
                    'Resource does not exist'
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
