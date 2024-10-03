<?php


namespace App\Modules\AdminModule\Services;

use App\Models\ModerationRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ModerationLog;

class ModerationRequestService
{
    public function getAll (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;

        $query = ModerationRequest::connect(config('database.secondary'))
                    ->select(
                        'id',
                        'moderatable_type',
                        'moderatable_id',
                        'priority', 
                        'created_by',
                        'is_closed',
                        'closed_at',
                        'closed_by',
                )
                ->orderBy('created_at', 'DESC');
        
        
        $dataSet = array();
        if($per_page_items != 0 ){
            $dataSet = $query->paginate($per_page_items);
        }else{
            $dataSet = $query->get();
        }

        return $dataSet;
    }

    public function get ($mordaration_id){
        $data = ModerationRequest::connect(config('database.secondary'))
                  ->where('moderation_requests.id', $mordaration_id)
                ->first();

        $query = ModerationRequest::connect(config('database.secondary'))
                ->join('users', 'users.id', '=', 'moderation_requests.created_by')
                ->leftjoin('users as morderation_user', 'morderation_user.id', '=', 'moderation_requests.moderatable_id')
                ->where('moderation_requests.id', $mordaration_id);
                
        if($data->closed_by != null){

           $query = $query->join('users as closer', 'closer.id', '=', 'moderation_requests.closed_by')
                    ->select(
                        'moderation_requests.id as moderation_request_id', // Renaming to avoid ambiguity
                        'moderation_requests.moderatable_type',
                        'moderation_requests.moderatable_id',
                        'moderation_requests.priority',
                        'users.first_name as created_by',
                        'moderation_requests.is_closed',
                        'moderation_requests.closed_at',
                        'moderation_requests.closed_by',
                        'closer.first_name as closed_by',
                        'morderation_user.display_name as user_name',
                        'morderation_user.email as user_email',
                        'morderation_user.is_approved as is_approved'
                    );
        }else{
            $query = $query->select(
                        'moderation_requests.id as moderation_request_id', // Renaming to avoid ambiguity
                        'moderation_requests.moderatable_type',
                        'moderation_requests.moderatable_id',
                        'moderation_requests.priority',
                        'users.first_name as created_by',
                        'moderation_requests.is_closed',
                        'moderation_requests.closed_at',
                        'moderation_requests.closed_by',
                        'moderation_requests.closed_by',
                        'morderation_user.display_name as user_name',
                        'morderation_user.email as user_email',
                         'morderation_user.is_approved as is_approved'
                    );
        }
      
      return   $query->first();
    
    }

    public function close($mordaration_id, $request){
        ModerationRequest::connect(config('database.default'))
                   ->where('id', $mordaration_id)
                    ->update([
                        'is_closed' => true,
			            'closed_at' => Carbon::now(),
			            'closed_by' => auth()->id(),
                    ]);
        
        ModerationLog::connect(config('database.default'))
             ->create([
            'moderation_request_id' => $mordaration_id,
            'updated_by' => auth()->id(),
            'status' => 'closed'
        ]);
    }

    public function reopen($mordaration_id ,$request){
        
        ModerationRequest::connect(config('database.default'))
                ->where('id', $mordaration_id)
                ->update([
                    'is_closed' => true,
			        'closed_at' => null,
			        'closed_by' => null,
                ]);
            
        ModerationLog::connect(config('database.default'))
                ->create([
               'moderation_request_id' => $mordaration_id,
               'updated_by' => auth()->id(),
               'status' => 'opened'
        ]);
    
    }

    public function delete($mordaration_id){
       
        ModerationRequest::connect(config('database.default'))->destroy($mordaration_id);
        
    }

    public function userApprove(array $data){
       
        User::connect(config('database.default'))
            ->where('id', $data['user_id'])
            ->update([
                'is_approved' => true,
            ]);
        
    }

    public function getAllModerationLog($moderation_id){
      return   ModerationLog::connect(config('database.secondary'))
                  ->where('moderation_request_id', $moderation_id)
                  ->join('users', 'users.id', '=', 'moderation_logs.updated_by')
                  ->select(
                    'moderation_logs.id',
                    'moderation_logs.moderation_request_id',
                    'moderation_logs.updated_at',
                    'moderation_logs.status',
                    'moderation_logs.created_at',
                    'users.first_name'
                    )
                  ->get();
    }

    public function getAllModerationOpenCount(){

        return ModerationRequest::connect(config('database.secondary'))
                    ->where('moderation_requests.is_closed', false)
                    ->count();

      }
}