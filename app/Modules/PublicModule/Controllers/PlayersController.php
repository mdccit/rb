<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\PublicModule\Services\PlayerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayersController extends Controller
{
    private $playerService;

    function __construct()
    {
        //Init models
        $this->playerService = new PlayerService();
    }

    public function updateBio(Request $request,$user_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'bio' => 'required|string|min:3|max:5000',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->playerService->updateBio($request->all(),$user_slug);

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

    public function updatePersonalOtherInfo(Request $request,$user_slug)
    {
        try{
            $validator = Validator::make($request->all(), [
                'address_line_1' => 'nullable|string|max:80',
                'address_line_2' => 'nullable|string|max:80',
                'city' => 'nullable|required_with:address_line_1|string|max:48',
                'state_province' => 'nullable|required_with:address_line_1|string|max:48',
                'postal_code' => 'nullable|required_with:address_line_1|string|max:24',

                'country' => 'required|numeric',

                'phone_number' => 'required|string|max:15',
                'phone_code_country' => 'required|numeric',

                'nationality' => 'required|numeric',
                'gender' => 'required|string|in:male,female,other',
                'date_of_birth' => 'required|date',

                'handedness' => 'nullable|string|in:left,right,both',
                'preferred_surface' => 'nullable|string|in:hard,clay,grass,artificial',

                'weight_in_kg' => 'boolean',
                'weight_kg' => 'nullable|numeric',
                'weight_lb' => 'nullable|numeric',

                'height_in_cm' => 'boolean',
                'height_cm' => 'nullable|numeric',
                'height_ft' => 'nullable|numeric',
                'height_in' => 'nullable|numeric',

                'graduation_month_year' => 'required|date',
            ]);
            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }

            $this->playerService->updatePersonalOtherInfo($request->all(),$user_slug);

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
}
