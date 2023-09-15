<?php

namespace hpsynapse\moduser\Controllers;

use Illuminate\Http\Request;
use hpsynapse\moduser\Facades\UserRepo;
use hpsynapse\moduser\Facades\RoleRepo;
use hpsynapse\moduser\Facades\UserAuth;

use hpsynapse\moduser\Jobs\BroadcastNotif;

use App\Base\BaseController;

use App\Events\SendData;

class BroadcastController extends BaseController
{
    protected $accessRuleKey = 'moduser.broadcast';

    public function __construct()
    {
        // $this->forceApiOutput();
    }

    public function index(Request $request)
    {

    }
    
    public function sendBroadcast(Request $request)
    {
        event(new SendData('data'));
        return response()->json([
            'data' => 'test'
        ]);

        if(!UserAuth::hasAccess($this->accessRuleKey,'c')){
            $this->setError(__('alert.access_denied',false,403));
            return $this->done();
        }

        $input = $request->only(['title','description','message']);
        BroadcastNotif::dispatch(UserAuth::user('id'),false,$input['title'],$input['description'],$input['message']);
        $this->output['message'] = 'Broadcast berhasil dikirim';
        return $this->done();
    }
}