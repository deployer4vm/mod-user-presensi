<?php

namespace hpsynapse\moduser\Controllers\Firebase;

// 1. Import level PHP

// 2. Import level Package Composer

// 3. Import level Laravel Core
use Illuminate\Http\Request;

// 4. Import level Synapse Core
use App\Base\BaseController;

// 5. Import level Synapse Module Package

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
use hpsynapse\moduser\Facades\UserNotifRepo;
use Illuminate\Support\Facades\Validator;


class FirebaseController extends BaseController
{

    public function __construct()
    {
        $this->forceApiOutput();
    }

    /**
     * Update token firebase
     * /api/auth/update/firebase/token
     *
     * @param Request $request
     */
    public function updateFirebaseToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_token' => 'required',
            'new_token' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setError($validator->errors()->first());
            return $this->done();
        }

        $data = UserNotifRepo::updateTokenFirebase($request->input('old_token'), $request->input('new_token'));
        $this->setData($data);

        return $this->done();
    }
}
