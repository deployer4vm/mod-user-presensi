<?php

namespace hpsynapse\moduser\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use hpsynapse\moduser\Facades\UserAuth;
// use hpsynapse\moduser\Facades\UserRepo;
// use hpsynapse\moduser\Models\User;
use hpsynapse\moduser\Models\ApiToken;

/**
 * Untuk handle auth di tampilan web dengan menyertakan syn_api_webauth di GET request.
 * string syn_api_webauth didapatkan dari helper UserAuth.getApiWebToken() di vue
 */
class ApiWebAuth
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
        $synApiWebauth=json_decode(UserAuth::decryptCredential($request->input('syn_api_webauth',false),'webauth.876tfvbhju76tfghu765tg273td7237yf732='),true);
        
        $request->query->remove('syn_api_webauth');
        $request->headers->add(['X-Client-Key' => $synApiWebauth['client_key']]);
                
        if(!($token = ApiToken::where('api_token',$synApiWebauth['token'])->first())){
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

        Auth::setUser($token);
        UserAuth::setInit(true);
    
        return $next($request);
    }
    
}
