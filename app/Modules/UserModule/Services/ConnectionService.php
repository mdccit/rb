<?php


namespace App\Modules\UserModule\Services;


use App\Models\ConnectionRequest;


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




}
