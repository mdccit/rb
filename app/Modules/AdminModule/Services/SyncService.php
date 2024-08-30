<?php


namespace App\Modules\AdminModule\Services;


use App\Models\School;
use GuzzleHttp\Client;
use App\Models\SyncLog;

class SyncService
{
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


    public function matchResult (array $data){
        // Get results from API
        $client = new Client();

        $response = $client->request('GET', 'https://api.data.gov/ed/collegescorecard/v1/schools', [
            'query' => [
                'school.name' => $data['search'],
            ],
            'headers' => [
                'X-Api-Key' => config('us-gov.api_key'),
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
        
            return [
                'result' => null,
                'result_count' => null,
                'message' =>'An error occurred while fetching data from the API.'
            ];
        }

        $response_results = json_decode($response->getBody()->getContents(), true);

        // Now we need to format this into a nice array
        $results = [];

        foreach ($response_results['results'] as $result) {
            $results[] = [
                'id' => $result['id'],
                'name' => $result['school']['name'],
                'alias' => $result['school']['alias'],
                'city' => $result['school']['city'],
                'state' => $result['school']['state'],
                'zip' => $result['school']['zip'],
                'school_url' => $result['school']['school_url'],
           ];
       }

       $results_count = count($results);

       return [
            'result' => $results,
            'result_count' => $results_count,
            'message' =>'Success'
       ];

    }

    public function connect ($data,$school_id){
       // Get results from API
       $client = new Client();

       $response = $client->request('GET', 'https://api.data.gov/ed/collegescorecard/v1/schools', [
           'query' => [
               'id' => $data['gov_id'],
           ],
           'headers' => [
               'X-Api-Key' => config('us-gov.api_key'),
           ],
       ]);

       if ($response->getStatusCode() !== 200) {

           return [
               'message' =>'An error occurred while fetching data from the API.'
           ];
       }

       $response_results = json_decode($response->getBody()->getContents(), true);

       // Check if there are any results
       if (count($response_results['results']) === 0) {
        
           return [
            'message' =>'No results found for the provided ID.'
           ];
       }

       $result = $response_results['results'][0];

       // Check if this ID is already connected to another school
       $existingSchool = School::connect(config('database.secondary'))->where('gov_id', $result['id'])->first();
       if ($existingSchool) {
        
            return [
               'message' =>'This ID is already connected to another school.'
            ];
       }

       School::connect(config('database.default'))
                ->where('id', $school_id)
                ->update([
                   'gov_id' => $result['id'],
                ]);

       return [
        'gov_id' => $result['id'],
        'message' =>'Success'
    ];
       

    }

    public function disconnect($school_id){

        School::connect(config('database.default'))
                ->where('id', $school_id)
                ->update([
                   'gov_id' => null,
                ]);
    }

    public function sync($school_id){

       $school = School::connect(config('database.secondary'))->where('id', $school_id)->first();

       $lastSync = SyncLog::connect(config('database.secondary'))->latest()->first();

        // Create new synclog
        $syncLog = SyncLog::connect(config('database.default'))->create([
            'school_id' => $school_id,
            'status' => 'in-progress',
            'created_by' => auth()->id(),
        ]);

        // Get results from API
        $client = new Client();

        $response = $client->request('GET', 'https://api.data.gov/ed/collegescorecard/v1/schools', [
            'query' => [
                'id' => $school->gov_id,
            ],
            'headers' => [
                'X-Api-Key' => config('us-gov.api_key'),
            ],
        ]);

        if ($response->getStatusCode() !== 200) {

            SyncLog::connect(config('database.default'))
                ->where('id', $syncLog->id)
                ->update([
                   'status' => 'failed',
			       'data' => 'An error occurred while fetching data from the API.',
			      
                ]);
           
            return [
                'message' =>'An error occurred while fetching data from the API.'
            ];
            
        }

        $response_results = json_decode($response->getBody()->getContents(), true);

        // Check if there are any results
        if (count($response_results['results']) === 0) {
            SyncLog::connect(config('database.default'))
                ->where('id', $syncLog->id)
                ->update([
                   'status' => 'failed',
			       'data' => 'No results found for the provided ID.',
			      
                ]);
           
            return [
                'message' =>'No results found for the provided ID.'
            ];
           
        }

        $result = $response_results['results'][0];

        // Get the gov_sync_settings from the school
        $govSyncSettings = json_decode($school->gov_sync_settings, true);

        // Initialize $updateData array
        $updateData = [];

        // If $govSyncSettings is not provided, consider all fields for synchronization
        if (!$govSyncSettings) {
            $govSyncSettings = array_keys($this->fieldMapping);
        }

        // Loop over the field mapping, and if the field is enabled in the gov_sync_settings, add it to the $updateData array
        foreach ($this->fieldMapping as $field => $path) {
            if (in_array($field, $govSyncSettings)) {

                // If it's disabled by default, check if the user has enabled it
                if (in_array($field, $this->disabledFieldsByDefault)) {
                    if (!data_get($govSyncSettings, $field)) {
                        continue;
                    }
                }

                // If the field is 'degrees_offered', filter the array to only include keys where the value is 1
                if ($field === 'degrees_offered') {
                    $degrees = data_get($result, $path);
                    $degrees = array_filter($degrees, function ($value) {
                        return $value === 1;
                    });
                    $updateData[$field] = array_keys($degrees);
                    // If field is percentage of international students
                } elseif ($field === 'percentage_of_international_students') {
                    // The returned is actually born in the US, so we need to get the opposite. Eg we have 80% but should be 20%
                    $updateData[$field] = 100 - data_get($result, $path);
                } elseif ($field === 'state') {
                    $updateData[$field] = strtolower(data_get($result, $path));
                } else {
                    $updateData[$field] = data_get($result, $path);
                }
            }
        }

        // Finally, update the school with the selected fields
        School::connect(config('database.default'))
                ->where('id', $school_id)
                 ->update([
                    'other_data' => $updateData,
               ]);

        SyncLog::connect(config('database.default'))
               ->where('id', $syncLog->id)
               ->update([
                  'status' => 'success',
                   'data' => json_encode($result),
                 
               ]);
    

        // Let's now just quickly check if the last sync was identical to this one and if so, we can just delete the one we just created + update the last one
        if ($lastSync && $lastSync->data === json_encode($result)) {
            $syncLog->delete();
            SyncLog::connect(config('database.default'))->destroy($syncLog->id);

            SyncLog::connect(config('database.default'))
                ->where('id', $lastSync->id)
                ->update([
                    'created_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            
        }
    }

    public function updateSetting ($data, $school_id){

        foreach (array_keys($this->fieldMapping) as $field) {
            if (!isset($data['gov_sync_settings'][$field])) {
                $data['gov_sync_settings'][$field] = false;
            }
        }

        School::connect(config('database.default'))
                ->where('id', $school_id)
                 ->update([
                    'gov_sync_settings' =>  $data['gov_sync_settings'],
               ]);
    }

    public function history($school_id){

        
        return SyncLog::connect(config('database.secondary'))
                    ->where('school_id', $school_id)
                    ->join('users', 'users.id', '=' ,'sync_logs.created_by')
                    ->select(
                        'sync_logs.id',
                        'sync_logs.school_id',
                        'sync_logs.status',
                        'sync_logs.data',
                        'users.first_name as created_by',
                        'sync_logs.created_at',
                        'sync_logs.updated_at'
                    )
                    ->get();
        
    }
    

}
