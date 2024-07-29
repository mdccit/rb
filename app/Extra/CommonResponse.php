<?php


namespace App\Extra;


class CommonResponse
{
    public static function getResponse(
        $status,
        $message,
        $display_message,
        $response_data = null
    ){
        if($response_data == null){
            return response()->json([
                'status'=>$status,
                'message' => $message,
                'display_message' => $display_message],$status);
        } else {
            return response()->json([
                'status'=>$status,
                'message' => $message,
                'display_message' => $display_message,
                'data' => $response_data ],$status);
        }

    }
}
