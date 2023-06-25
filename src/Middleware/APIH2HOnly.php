<?php

namespace hpsynapse\moduser\Middleware;

use Closure;
// use Illuminate\Support\Facades\Auth;
use hpsynapse\moduser\Facades\UserAuth;
use Illuminate\Support\Facades\Auth;

/**
 * 
 */
class APIH2HOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!UserAuth::isH2H()) {
            return response([
                'status' => 403002,
                'message' => 'Host to Host Token required for this action',
                'params' => [],
                'errors' => null,
                'data' => false
            ], 403);
        }
        return $next($request);
    }
    
}
