<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header("Lang",config('app.fallback_locale'));

        if(!in_array($locale, config('app.accepted_locales'))){
            $locale = session('locale') ?: config('app.fallback_locale');
        }
        session(['locale' => $locale]);
        app()->setLocale($locale);

        return $next($request);
    }
}
