<?php


namespace App\Modules\UserModule\Services;


use App\Models\ConnectionRequest;
use App\Models\User;
use App\Traits\AzureBlobStorage;
use Illuminate\Support\Facades\DB;
use App\Models\Conversation;

class ConnectionService
{
    use AzureBlobStorage;

    public function requestConnection (array $data){

        ConnectionRequest::connect(config('database.default'))
            ->create([
                'sender_id' => auth()->id(),
                'receiver_id' => $data['receiver_id'],
                'connection_status' => 'pending'
        ]);
        
    }

    public function connectionStatusUpdate(array $data, $connection_id){
        
        $removed_by = $data['connection_status'] == "removed" ? auth()->id() :null;
        
        ConnectionRequest::connect(config('database.default'))
            ->where('id', $connection_id)
            ->update([
                'connection_status' => $data['connection_status'],
			    'removed_by' => $removed_by,

            ]);
       
    }

    public function userinivitationAndConnectedList (){

        $invitation_list =   ConnectionRequest::connect(config('database.secondary'))
                                ->where('receiver_id', auth()->id())
                                ->where('connection_status','=','pending')
                                ->get();
        
        $connected  = ConnectionRequest::connect(config('database.secondary'))
                        ->where('connection_status','=','accepted')
                        ->where('receiver_id', auth()->id())
                        ->orWhere('sender_id', auth()->id())
                        ->get();
        return [
            'invitation_list' => $invitation_list,
            'connected_list' => $connected
        ];
    }

    public function userConnectionList($user_id){

        $user_connection = ConnectionRequest::connect(config('database.secondary'))
                            ->where('connection_status', 'accepted')
                            ->where(function ($query) use ($user_id) {
                                 $query->where('receiver_id', $user_id)
                                 ->orWhere('sender_id', $user_id);
                            })
                            ->get();
        
       $receiver_user_ids = $user_connection->pluck('receiver_id')->reject(function ($id) use ($user_id) {
                            return $id == $user_id;
                        });
                            
        $senderIds = $user_connection->pluck('sender_id')->reject(function ($id) use ($user_id) {
                        return $id == $user_id;
                    });

        $user_connection_ids = $receiver_user_ids->merge($senderIds)->unique();      

       
        $connections = [];
     
        if($user_id != auth()->id()){
           $auth_user_connection = ConnectionRequest::connect(config('database.secondary'))
                                   ->where('connection_status', '!=', 'removed')
                                   ->where(function ($query) {
                                       $query->where('receiver_id', auth()->id())
                                        ->orWhere('sender_id', auth()->id());
                                   })
                                   ->get();
            $auth_user_id = auth()->id();
            $receiver_ids = $auth_user_connection->pluck('receiver_id')->reject(function ($id) use ($auth_user_id) {
                return $id == $auth_user_id;
            });
            
            $sender_ids = $auth_user_connection->pluck('sender_id')->reject(function ($id) use ($auth_user_id) {
                return $id == $auth_user_id;
            });
            $auth_user_connection_ids = $receiver_ids->merge($sender_ids)->unique();      
                  
            foreach( $user_connection_ids as  $user_connection_id){
                $query = User::connect(config('database.secondary'))
                            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                            ->leftJoin('user_addresses', 'user_addresses.user_id', '=' ,'users.id')
                            ->leftJoin('countries', 'countries.id', '=' ,'user_addresses.country_id')
                            ->leftJoin('players', 'players.user_id', '=', 'users.id')
                            ->where('users.id', $user_connection_id) 
                            ->select(
                                'users.id as id',
                                'users.slug as 	slug',
                                'users.display_name as name', 
                                'user_roles.name as role',
                                'user_addresses.city as city',
                                'countries.name as country',
                                'players.other_data as other_data'
                            );
            
                if ($query->count() > 0){
                    $query->leftJoin('sports', 'sports.id', '=', 'players.sport_id')
                           ->addSelect('sports.name as sport_name');
                }

                $user =  $query->first();
                $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
                $user->profile_picture =$profile_picture;
                $user->connection_status ='connect';

                foreach($auth_user_connection_ids as $auth_user_connection_id){
                  
                    
                    if($user_connection_id == $auth_user_connection_id){
                        $connect = ConnectionRequest::connect(config('database.secondary'))
                                   ->where(function ($query) {
                                       $query->where('receiver_id', auth()->id())
                                        ->orWhere('sender_id', auth()->id());
                                   })
                                    ->where(function ($query) use ($auth_user_connection_id)  {
                                     $query->where('receiver_id', $auth_user_connection_id)
                                       ->orWhere('sender_id', $auth_user_connection_id);
                                    })
                                   ->first();

                         $user->connection_status = $connect->connection_status;
                         $user->connection_id = $connect->id;
                    }

                    

                }

                $connections[] = $user;

            }
           

        }else{
            foreach( $user_connection_ids as  $user_connection_id){
                $query = User::connect(config('database.secondary'))
                           ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                           ->leftJoin('user_addresses', 'user_addresses.user_id', '=' ,'users.id')
                           ->leftJoin('countries', 'countries.id', '=' ,'user_addresses.country_id')
                           ->leftJoin('players', 'players.user_id', '=', 'users.id')
                           ->where('users.id', $user_connection_id) 
                           ->select(
                                'users.id as id',
                                'users.slug as 	slug',
                                'users.display_name as name', 
                                'user_roles.name as role',
                                'user_addresses.city as city',
                                'countries.name as country',
                                'players.other_data as other_data'
                           );
                if ($query->count() > 0){
                    $query->leftJoin('sports', 'sports.id', '=', 'players.sport_id')
                          ->addSelect('sports.name as sport_name');
                }
        
                $user =  $query->first();
                $profile_picture = $this->getSingleFileByEntityId($user->id,'user_profile_picture');
                $user->profile_picture =$profile_picture;
                
                $connect = ConnectionRequest::connect(config('database.secondary'))
                          ->where(function ($query) {
                              $query->where('receiver_id', auth()->id())
                               ->orWhere('sender_id', auth()->id());
                          })
                           ->where(function ($query) use ($user_connection_id)  {
                            $query->where('receiver_id', $user_connection_id)
                              ->orWhere('sender_id', $user_connection_id);
                           })
                          ->first();

                $user->connection_status = $connect->connection_status;
                $user->connection_id = $connect->id;

                $connections[] = $user;
            }
        }

           return [
               'connection'=> $connections,
                'count' => count($connections)
           ];


                       
        }

        public function checkConnectionType ($user_id){
            
            $existing = ConnectionRequest::connect(config('database.secondary'))
                        ->whereIn('connection_status',['pending','accepted'])
                        ->where(function ($query) {
                            $query->where('receiver_id', auth()->id())
                            ->orWhere('sender_id', auth()->id());
                        })
                        ->where(function ($query) use ($user_id)  {
                            $query->where('receiver_id', $user_id)
                            ->orWhere('sender_id', $user_id);
                        })
                        ->exists();
            $type =null;
            if($existing){
                $type = ConnectionRequest::connect(config('database.secondary'))
                            ->whereIn('connection_status',['pending','accepted'])
                            ->where(function ($query) {
                                $query->where('receiver_id', auth()->id())
                                ->orWhere('sender_id', auth()->id());
                            })
                            ->where(function ($query) use ($user_id)  {
                                $query->where('receiver_id', $user_id)
                               ->orWhere('sender_id', $user_id);
                            })
                            ->first();
            }

            return [
                'connection' => $existing,
                'type' => $type
            ];
        }

        public function invitationSendList(){
            $invitation_list = ConnectionRequest::connect(config('database.secondary'))
                            ->join('users','users.id','=','connection_requests.receiver_id')
                            ->join('countries', 'countries.id', '=' ,'users.country_id')
                            ->whereIn('connection_status',['pending'])
                            ->where('sender_id', auth()->id())
                            ->select([
                                'connection_requests.id as id',
                                'users.id as user_id',
                                'users.display_name as name',
                                'countries.name as country',
                                'users.slug as 	slug',
                                'users.user_role_id as role_id',
                              
                            ])
                            ->addSelect(DB::raw('IF((SELECT other_data FROM players WHERE user_id = connection_requests.receiver_id ) IS NULL,NULL,(SELECT other_data FROM players WHERE user_id = connection_requests.receiver_id )) as other_data'))
                            ->addSelect(DB::raw('IF((SELECT city FROM user_addresses WHERE user_id = connection_requests.receiver_id ) IS NULL,NULL,(SELECT city FROM user_addresses WHERE user_id = connection_requests.receiver_id)) as city'))
                            ->get();
                        
            foreach( $invitation_list as $key=> $data){
                $profile_picture = $this->getSingleFileByEntityId($data->user_id,'user_profile_picture');
                $invitation_list[$key]['profile_picture'] =$profile_picture;
            }

            $acccept_list = ConnectionRequest::connect(config('database.secondary'))
                           ->join('users','users.id','=','connection_requests.receiver_id')
                           ->join('countries', 'countries.id', '=' ,'users.country_id')
                           ->join('users as sender_users','sender_users.id','=','connection_requests.sender_id')
                           ->join('countries as sender_countries', 'sender_countries.id', '=' ,'users.country_id')
                           ->whereIn('connection_status',['accepted'])
                                ->where(function ($query) {
                                    $query->where('receiver_id', auth()->id())
                                    ->orWhere('sender_id', auth()->id());
                            })
                            ->select([
                                'connection_requests.id as id',
                                'users.id as receiver_id',
                                'users.display_name as receiver_name',
                                'countries.name as receiver_country',
                                'users.slug as 	receiver_slug',
                                'users.user_role_id as receiver_role_id',
                                'sender_users.id as sender_id',
                                'sender_countries.name as sender_country',
                                'sender_users.slug as 	sender_slug',
                                'sender_users.display_name as 	sender_name',
                                'sender_users.user_role_id as sender_role_id',
                            ])
                            ->addSelect(DB::raw('IF((SELECT other_data FROM players WHERE user_id = connection_requests.sender_id ) IS NULL,NULL,(SELECT other_data FROM players WHERE user_id = connection_requests.sender_id )) as sender_other_data'))
                            ->addSelect(DB::raw('IF((SELECT city FROM user_addresses WHERE user_id = connection_requests.sender_id ) IS NULL,NULL,(SELECT city FROM user_addresses WHERE user_id = connection_requests.sender_id)) as sender_city'))
                            ->addSelect(DB::raw('IF((SELECT other_data FROM players WHERE user_id = connection_requests.receiver_id ) IS NULL,NULL,(SELECT other_data FROM players WHERE user_id = connection_requests.receiver_id )) as receiver_other_data'))
                            ->addSelect(DB::raw('IF((SELECT city FROM user_addresses WHERE user_id = connection_requests.receiver_id ) IS NULL,NULL,(SELECT city FROM user_addresses WHERE user_id = connection_requests.receiver_id)) as receiver_city'))
                           ->get();
            foreach( $acccept_list as $key=> $data){
                $receiver_profile_picture = $this->getSingleFileByEntityId($data->receiver_id,'user_profile_picture');
                $sender_profile_picture = $this->getSingleFileByEntityId($data->sender_id,'user_profile_picture');

                $acccept_list[$key]['receiver_profile_picture'] =$receiver_profile_picture;
                $acccept_list[$key]['sender_profile_picture'] =$sender_profile_picture;

            }
            $invite_liste = ConnectionRequest::connect(config('database.secondary'))
                            ->join('users','users.id','=','connection_requests.sender_id')
                            ->leftJoin('countries', 'countries.id', '=' ,'users.country_id')
                            ->whereIn('connection_status',['pending'])
                            ->where('receiver_id', auth()->id())
                            ->select([
                               'connection_requests.id as id',
                               'users.id as user_id',
                               'users.display_name as name',
                               'countries.name as country',
                               'users.slug as 	slug',
                               'users.user_role_id as role_id',
                           ])
                           ->addSelect(DB::raw('IF((SELECT other_data FROM players WHERE user_id = connection_requests.sender_id ) IS NULL,NULL,(SELECT other_data FROM players WHERE user_id = connection_requests.sender_id )) as other_data'))
                           ->addSelect(DB::raw('IF((SELECT city FROM user_addresses WHERE user_id = connection_requests.sender_id ) IS NULL,NULL,(SELECT city FROM user_addresses WHERE user_id = connection_requests.sender_id)) as city'))
                          ->get();
            foreach( $invite_liste as $key=> $data){
                $profile_picture = $this->getSingleFileByEntityId($data->user_id,'user_profile_picture');
                $invite_liste[$key]['profile_picture'] =$profile_picture;
            }

            return [
                    'invite_list' => $invitation_list,
                    'acccept_list' => $acccept_list,
                    'invitation_list' => $invite_liste 
                ];
        }

        public function conversationRemove($connection_id){
            $conection  = ConnectionRequest::connect(config('database.secondary'))
                            ->where('id',$connection_id)
                           ->first();

          $existing =  Conversation::connect(config('database.secondary'))
                    ->where(function ($query) use ($conection) {
                        $query->where('conversations.user1_id', $conection->sender_id)
                         ->where('conversations.user2_id',$conection->receiver_id);
                    })
                    ->orWhere(function ($query) use ($conection) {
                        $query->where('conversations.user1_id',$conection->receiver_id)
                        ->Where('conversations.user2_id', $conection->sender_id);
                    })
                   ->exists();
          if($existing){
            $conversation =  Conversation::connect(config('database.secondary'))
                        ->where(function ($query) use ($conection) {
                            $query->where('conversations.user1_id', $conection->sender_id)
                            ->where('conversations.user2_id',$conection->receiver_id);
                        })
                        ->orWhere(function ($query) use ($conection) {
                            $query->where('conversations.user1_id',$conection->receiver_id)
                           ->Where('conversations.user2_id', $conection->sender_id);
                        })
                        ->first();
              Conversation::connect(config('database.default'))->destroy($conversation->id);    

            }
        }
    }

    





