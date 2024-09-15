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

        $invitationList =   ConnectionRequest::connect(config('database.secondary'))
                                ->where('receiver_id', auth()->id())
                                ->where('connection_status','=','pending')
                                ->get();
        
        $connected  = ConnectionRequest::connect(config('database.secondary'))
                        ->where('connection_status','=','accepted')
                        ->where('receiver_id', auth()->id())
                        ->orWhere('sender_id', auth()->id())
                        ->get();
        return [
            'invitation_list' => $invitationList,
            'connected_list' => $connected
        ];
    }

    public function userConnectionList($user_id){

        $userConnection = ConnectionRequest::connect(config('database.secondary'))
                            ->where('connection_status', 'accepted')
                            ->where(function ($query) use ($user_id) {
                                 $query->where('receiver_id', $user_id)
                                 ->orWhere('sender_id', $user_id);
                            })
                            ->get();
        
       $receiverUserIds = $userConnection->pluck('receiver_id')->reject(function ($id) use ($user_id) {
                            return $id == $user_id;
                        });
                            
        $senderIds = $userConnection->pluck('sender_id')->reject(function ($id) use ($user_id) {
                        return $id == $user_id;
                    });

        $userConnectionIds = $receiverUserIds->merge($senderIds)->unique();      

       
        $connections = [];
     
        if($user_id != auth()->id()){
           $authUserConnection = ConnectionRequest::connect(config('database.secondary'))
                                   ->where('connection_status', '!=', 'removed')
                                   ->where(function ($query) {
                                       $query->where('receiver_id', auth()->id())
                                        ->orWhere('sender_id', auth()->id());
                                   })
                                   ->get();
            $authUserId = auth()->id();
            $receiverIds = $authUserConnection->pluck('receiver_id')->reject(function ($id) use ($authUserId) {
                return $id == $authUserId;
            });
            
            $senderIds = $authUserConnection->pluck('sender_id')->reject(function ($id) use ($authUserId) {
                return $id == $authUserId;
            });
            $authUserConnectionIds = $receiverIds->merge($senderIds)->unique();      
                  
            foreach( $userConnectionIds as  $userConnectionId){
                $user = User::connect(config('database.secondary'))
                            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                            ->leftJoin('user_addresses', 'user_addresses.user_id', '=' ,'users.id')
                            ->leftJoin('countries', 'countries.id', '=' ,'user_addresses.country_id')
                            ->leftJoin('players', 'players.user_id', '=', 'users.id')
                            ->where('users.id', $userConnectionId) 
                            ->select(
                                'users.id as id',
                                'users.display_name as name', 
                                'user_roles.name as role',
                                'user_addresses.city as city',
                                'countries.name as country',
                                'players.other_data as other_data'
                            )
                            ->first();

                $user->connection_status ='connect';

                foreach($authUserConnectionIds as $authUserConnectionId){
                  
                    
                    if($userConnectionId == $authUserConnectionId){
                        $connect = ConnectionRequest::connect(config('database.secondary'))
                                   ->where(function ($query) {
                                       $query->where('receiver_id', auth()->id())
                                        ->orWhere('sender_id', auth()->id());
                                   })
                                    ->where(function ($query) use ($authUserConnectionId)  {
                                     $query->where('receiver_id', $authUserConnectionId)
                                       ->orWhere('sender_id', $authUserConnectionId);
                                    })
                                   ->first();

                         $user->connection_status = $connect->connection_status;
                    }

                    

                }

                $connections[] = $user;

            }
           

        }else{
            foreach( $userConnectionIds as  $userConnectionId){
                $user = User::connect(config('database.secondary'))
                           ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
                           ->leftJoin('user_addresses', 'user_addresses.user_id', '=' ,'users.id')
                           ->leftJoin('countries', 'countries.id', '=' ,'user_addresses.country_id')
                           ->leftJoin('players', 'players.user_id', '=', 'users.id')
                           ->where('users.id', $userConnectionId) 
                           ->select(
                                'users.id as id',
                                'users.display_name as name', 
                                'user_roles.name as role',
                                'user_addresses.city as city',
                                'countries.name as country',
                                'players.other_data as other_data'
                           )
                          ->first();
                $connect = ConnectionRequest::connect(config('database.secondary'))
                          ->where(function ($query) {
                              $query->where('receiver_id', auth()->id())
                               ->orWhere('sender_id', auth()->id());
                          })
                           ->where(function ($query) use ($userConnectionId)  {
                            $query->where('receiver_id', $userConnectionId)
                              ->orWhere('sender_id', $userConnectionId);
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
    }

    





