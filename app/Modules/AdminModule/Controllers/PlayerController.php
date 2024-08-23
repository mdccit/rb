<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\PlayerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Player;

class PlayerController extends Controller
{
    private $playerService;

    function __construct()
    {
        //Init models
        $this->playerService = new PlayerService();
    }

    

    public function getUser($user_id)
    {
        try{

            $player = Player::connect(config('database.default'))
                       ->where('user_id', $user_id)->first();
            if($player){
                $responseData = $this->playerService->getUser($user_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully fetched',
                    'Successfully fetched',
                    $responseData
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'Player does not exist',
                    'Player does not exist'
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

    
    public function updateUser(Request $request,$user_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'gender' => 'nullable|string|in:male,female,other',
                'date_of_birth' => 'nullable|date',
                'graduation_month_year' => 'nullable|date',
                'gpa' => 'nullable|numeric',
                'height_cm' => 'nullable|numeric',
                'weight' => 'nullable|numeric',
                'other_data' => 'nullable',
               
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $player = Player::connect(config('database.default'))
                     ->where('user_id', $user_id)->first();
            if($player){
               
               $this->playerService->updateUser($request->all(),$user_id);
               
                return CommonResponse::getResponse(
                        200,
                        'Successfully Updated',
                        'Successfully Updated'
                    );
            }else{

                return CommonResponse::getResponse(
                    422,
                    $e->getMessage(),
                    'Something went to wrong'
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
