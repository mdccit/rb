<?php

namespace App\Http\Middleware\Auth;

use App\Extra\CommonResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsDefaultUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check() && Auth::user()->isDefaultUser()){
            return $next($request);
        }else{
            return CommonResponse::getResponse(
                401,
                'Only Default user can access to this route',
                'Only Default user can access to this route'
            );
        }
    }
}
