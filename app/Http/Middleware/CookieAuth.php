<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CookieAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // if no authorization header but auth_token cookie exists,add it to headers
        if(!$request->hasHeader('Authorization') && $request->hasCookie('auth_token')){
            $token=$request->cookie('auth_token');
            $request->headers->set('Authorization', 'Bearer '.$token);
        }
        return $next($request);
    }
}
