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
        $accessKey = $request->header("access_key","");
        if(config('app.access_key') != $accessKey){
            return CommonResponse::getResponse(
                401,
                'Access Key Invalid',
                'Access Key Invalid'
            );
        }
        return $next($request);
    }
}
