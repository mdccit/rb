<?php

namespace App\Modules\AdminModule\Controllers;

use App\Extra\CommonResponse;
use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\School;

class SyncController extends Controller
{
    private $syncService;

    function __construct()
    {
        //Init models
        $this->syncService = new SyncService();
    }

     // Define a mapping of fields to their paths in the $result array
     protected $fieldMapping = [
        'name' => ['school', 'name'],
        'url' => ['school', 'school_url'],
        'tuition_in_state' => ['latest', 'cost', 'tuition', 'in_state'],
        'tuition_out_state' => ['latest', 'cost', 'tuition', 'out_of_state'],
        'cost_of_attendance' => ['latest', 'cost', 'avg_net_price', 'overall'],
        'degrees_offered' => ['latest', 'academics', 'program', 'degree'],
        'address' => ['school', 'address'],
        'city' => ['school', 'city'],
        'state' => ['school', 'state'],
        'zip' => ['school', 'zip'],
        // 'country' => [], // This is a static value, so we leave the array empty
        'coords_lat' => ['location', 'lat'],
        'coords_lng' => ['location', 'lon'],
        'acceptance_rate' => ['latest', 'admissions', 'admission_rate', 'overall'],
        'graduation_rate' => ['latest', 'completion', 'rate_suppressed', 'overall'],
        'student_count' => ['latest', 'student', 'size'],
        'earnings_1_year_after_graduation' => ['latest', 'earnings', '1_yr_after_completion', 'median'],
        'earnings_3_years_after_graduation' => ['latest', 'earnings', '4_yrs_after_completion', 'median'],
        'student_to_faculty_ratio' => ['latest', 'student', 'demographics', 'student_faculty_ratio'],
        'percentage_of_international_students' => ['latest', 'student', 'demographics', 'share_born_US', 'home_ZIP'],
    ];

    protected $disabledFieldsByDefault = [
        'name'
    ];

    public function matchResult(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'search' => 'required|string|min:3|max:255',
            ]);

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $dataSets = $this->syncService->matchResult($request->all());

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

    public function connect(Request $request, $school_id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'gov_id' => 'required|numeric',
            ]);

            $school = School::connect(config('database.secondary'))->where('id', $school_id)->first();

             if($school->gov_id == null){

                $responseData = $this->syncService->connect($request->all(),$school_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully fetched',
                    'Successfully fetched',
                    $responseData
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'This school is already connected to an GOV ID',
                    'This school is already connected to an GOV ID'
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

    public function disconnect($school_id)
    {
        try{
            $school = School::connect(config('database.secondary'))->where('id', $school_id)->first();

            if($school){

                $responseData = $this->syncService->disconnect($school_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully disconnected',
                    'Successfully disconnected',
                    $responseData
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'School does not exist',
                    'School does not exist'
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

    public function sync($school_id)
    {
        try{

            $school = School::connect(config('database.secondary'))->where('id', $school_id)->first();

            if($school->gov_id !=null){

                $responseData = $this->syncService->sync($school_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully fetched',
                    'Successfully fetched',
                    $responseData
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'This school is not connected to the API.',
                    'This school is not connected to the API.'
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

    public function updateSetting(Request $request, $school_id)
    {
        try{
            $request->merge([
                'gov_sync_settings' => array_map(function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }, $request->input('gov_sync_settings', [])),
            ]);

            // Define validation rules
            $rules = [
                'gov_sync_settings' => [
                    'required',
                    'array',
                        function ($attribute, $value, $fail) {
                            foreach ($value as $key => $val) {
                                if (!in_array($key, array_keys($this->fieldMapping))) {
                                     $fail("$attribute.$key is not a valid key.");
                                }
                                if (!is_bool($val)) {
                                    $fail("$attribute.$key must be a boolean. Value given: " . var_export($val, true));
                               }
                            }
                        }
                ],
            ];

            $validator = Validator::make($request->all(), $rules);

            $validator->after(function ($validator) use ($request) {
                foreach (array_keys($request->input('gov_sync_settings', [])) as $field) {
                    if (!array_key_exists($field, $this->fieldMapping)) {
                        $validator->errors()->add('gov_sync_settings', "The selected gov_sync_settings.$field is invalid.");
                    }
                }
            });

            if ($validator->fails())
            {
                return CommonResponse::getResponse(
                    422,
                    $validator->errors()->all(),
                    'Input validation failed'
                );
            }

            $data = $validator->validated();


            $this->syncService->updateSetting($data, $school_id);

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

    public function history($school_id)
    {
        try{
            $school = School::connect(config('database.secondary'))->where('id', $school_id)->first();

            if($school){

                $responseData = $this->syncService->history($school_id);

                return CommonResponse::getResponse(
                    200,
                    'Successfully fetched',
                    'Successfully fetched',
                    $responseData
                );
            }else{
                return CommonResponse::getResponse(
                    422,
                    'School does not exist',
                    'School does not exist'
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
