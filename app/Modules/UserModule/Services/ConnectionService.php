<?php


namespace App\Modules\UserModule\Services;


use App\Models\ConnectionRequest;
use App\Models\User;


class ConnectionService
{
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
    }

    





