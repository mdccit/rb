<?php

namespace App\Http\Middleware\Custom;

use App\Extra\CommonResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $accessKey = $request->header("access_key","");
        // $accessKey = $request->server->get('access_key');
        $accessKey = \Request::header('access_key');
        error_log('access key header : '.$accessKey);
        error_log('access key app : '.config('app.access_key'));
        if(config('app.access_key') != $accessKey){
            // return CommonResponse::getResponse(
            //     401,
            //     'Access Key Invalid',
            //     'Access Key Invalid'
            // );
            return CommonResponse::getResponse(
                401,
                'access key header : '.$accessKey.' | lang : '.$request->header("lang",""),
                'access key app : '.config('app.access_key')
            );
        }
        return $next($request);
    }
}
