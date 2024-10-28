<?php

namespace hpsynapse\moduser\Middleware;

use Closure;
use hpsynapse\moduser\Facades\UserAuth;
// use Illuminate\Support\Facades\Auth;
use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 
 */
class ValidateClientKey
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
        if (
            config('AppConfig.packageLocal.moduser')
            && config('AppConfig.packageLocal.moduser.enable')
            && config('AppConfig.system.has_auth')
        ) {
            
            $clientKey = $request->header('X-Client-Key');
            $client = UserRepo::getUserSystem(['username', $clientKey]);
            
            // Check if Client Key valid
            if (!$client || !$client['system_user']) {
                return response([
                    'status' => 401001,
                    'message' => 'Unauthorized Client',
                    'params' => [],
                    'errors' => null,
                    'data' => false
                ], 401);
            }

            if (Auth::check()) {
                $authToken = Auth::user();
                $authUser = User::find($authToken->user_id);
                
                // Check if the request is a H2H request, the username of current user should match with the client key
                if ($authUser->system_user) {
                    if ($client['username'] == $authUser->username) {
                        $ipAddress = $request->ip();
                        if (
                            is_array($authUser->ip_address) 
                            && !in_array($ipAddress, $authUser->ip_address)
                        ) {
                            // if the request come from not allowed IP
                            return response([
                                'status' => 403003,
                                'message' => 'IP Address is not registered',
                                'params' => [],
                                'errors' => null,
                                'data' => false
                            ], 403);
                        }
                        UserAuth::setH2H();
                    } else {
                        // If not match return token error
                        return response([
                            'status' => 401002,
                            'message' => 'Invalid Client H2H Token',
                            'params' => [],
                            'errors' => null,
                            'data' => false
                        ], 401);
                    }
                }
            }

            UserAuth::setClient($client);
        }

        return $next($request);
    }
    
}
