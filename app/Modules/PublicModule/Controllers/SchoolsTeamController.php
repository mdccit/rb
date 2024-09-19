<?php

namespace App\Modules\PublicModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\PublicModule\Services\SchoolTeamService;
use Illuminate\Http\Request;
use App\Models\SchoolTeam;
use Illuminate\Support\Facades\Validator;
use App\Models\School;

class SchoolsTeamController extends Controller
{
    private $schoolTeamService;

    function __construct()
    {
        //Init models
        $this->schoolTeamService = new SchoolTeamService();
    }

    public function getSchoolTeamInfo($team_id)
    {
        try{
            $existing = SchoolTeam::connect(config('database.secondary'))
                            ->where('id',$team_id)
                            ->exists();
            
            if($existing){

                $responseData = $this->schoolTeamService->getSchoolTeamInfo($team_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully fetched',
                    'Successfully fetched',
                    $responseData
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'This team is not existing',
                    'This team is not existing'
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

    public function getSchoolTeam($school_id)
    {
        try{
            $existing = School::connect(config('database.secondary'))
                            ->where('id',$school_id)
                            ->exists();
            
            if($existing){

                $responseData = $this->schoolTeamService->getSchoolTeam($team_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully fetched',
                    'Successfully fetched',
                    $responseData
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'This school is not existing',
                    'This school is not existing'
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

    public function createSchoolTeam(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:45',
                'school_id' => 'required|exists:schools,id',
                'team_user' => 'required|array',
                'team_user.*.user_id' => 'required|exists:users,id',
                'team_user.*.status' => 'required|string|in:player,coache',
                'team_user.*.player_id' => 'nullable',
                'team_user.*.coache_id' => 'nullable'
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors(),
                    'Input validation failed'
                );
            }
          

            $responseData = $this->schoolTeamService->createSchoolTeam($request->all());

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

    public function destroy($team_id){
        try{
           
            $existing = SchoolTeam::connect(config('database.secondary'))
                            ->where('id',$team_id)
                            ->exists();
            
            if($existing){

                $this->schoolTeamService->destroy($team_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully Team Deleted',
                    'Successfully Team Deleted'           
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'This team is not existing',
                    'This team is not existing'
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
