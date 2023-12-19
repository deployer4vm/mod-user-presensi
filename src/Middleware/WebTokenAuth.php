<?php

namespace hpsynapse\moduser\Middleware;

use Closure;
// use Illuminate\Support\Facades\Auth;
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\UserRepo;

/**
 * 
 */
class WebTokenAuth
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
        $apiTokenData = $request->input('token');
        if(!($token = UserRepo::getToken($apiTokenData))){
            $isApi = $request->wantsJson() || $request->ajax();
            if ($isApi) {
                return response()->json([
                    'status' => 401,
                    'message' => __('alert.invalid_token'),
                    'data' => null,
                    'errors' => [true]
                ], 401);
            } else {
                return response()->view(
                    'error.generic',
                    [
                        'message' => __('alert.invalid_token'),
                        'code' => 401
                    ]
                );
            }
        }

        UserAuth::setUser($token['user_id'],$apiTokenData);
        return $next($request);
    }
    
}
