<?php

namespace hpsynapse\moduser\Services;

// 1. Import level PHP
use Exception;

// 2. Import level Package Composer

// 3. Import level Laravel Core
use Illuminate\Support\Facades\Log;

// 4. Import level Synapse Core
use App\Facades\Tenant;
use App\Base\BaseRepository;
use App\Facades\DbConfig;

// 5. Import level Synapse Module Package

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\AuthConfig;
use hpsynapse\moduser\Facades\UserRepo;

use hpsynapse\moduser\Models\User;
use hpsynapse\moduser\Models\AuthRequest;
use hpsynapse\moduser\Models\AuthFeature;
use hpsynapse\moduser\Models\AuthLog;
use hpsynapse\moduser\Models\AuthRequestArchive;

/**
 * @SuppressWarnings(PHPMD.ShortMethodNames)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Authenticator extends BaseRepository
{
    protected $autoResource = [
        'AuthRequest' => [
            'r' => AuthRequest::class,
            'w' => AuthRequest::class
        ],
        'AuthLog' => [
            'r' => AuthLog::class,
            'w' => AuthLog::class
        ],
    ];
    
    protected $autoResourceSearchField = [
        'AuthRequest' => ['description', 'request_code'],
    ];

    protected $autoResourceCreateValidate = [
        'AuthRequest' => [
            'tenant_id' => 'required',
            'feature_id' => 'required',
            'request_code' => 'required',
            // 'code' => 'required'
        ],
    ];

    protected $autoResourceUpdateValidate = [
        'AuthRequest' => [
            'tenant_id' => false,
            'feature_id' => false,
            'request_code' => 'required',
            // 'code' => 'required'
        ],
    ];

    /**
     * get list request2 permintaan grant yang aktif, berdasarkan yang dimintai
     * authentifikasinya
     * 
     * @param Int $grantUserId
     */
    public function listActiveGrantRequest($grantUserId = 0)
    {
        $now = now()->format('Y-m-d H:i:s');
        $model = AuthRequest::where('status', 0)->where('expired_time', '>=', $now);
        if ($grantUserId)
            $model = $model->where('grant_user_id',$grantUserId);

        $lists = $this->_list($model);

        foreach ($lists['data'] as $key => $value) {
            $lists['data'][$key]['feature'] = AuthFeature::find($value['feature_id'])->toArray();
        }

        return $lists;
    }

    /**
     * get active request
     * 
     * @param String $featureCode
     * @param String $requestCode
     * @param Int|False $userId
     */
    public function getActiveRequest($featureCode, $requestCode, $userId = false)
    {
        return $this->getRequest($featureCode, $requestCode, 0, $userId);
    }

        /**
         * get request
         * 
         * @param String $featureCode
         * @param String $requestCode
         * @param Int|False $status
         * @param Int|False $userId
         */
        private function getRequest($featureCode, $requestCode, $status = false, $userId = false)
        {
            // get Feature
            if (!($feature = $this->getActiveFeature($featureCode)))
                return false;

            // check expired
            $this->_autoResourceUpdate('updateAuthRequest', [
                [
                    ['feature_id', $feature->id],
                    ['request_code', $requestCode],
                    ['status', 0],
                    ['expired_time', '<=', now()->format('Y-m-d H:i:s')],                    
                ],
                ['status' => 3]
            ]);

            // get Request
            $where = [
                // 'with' => ['feature'],
                ['feature_id', $feature->id],
                ['request_code', $requestCode],
                [
                    ['status', [0, 1, 2]],
                    ['OR expired_time', '>' ,now()->format('Y-m-d H:i:s')],// atau ambil yg belum expired
                ],
                
            ];        

            if ($userId)
                $where[] = ['grant_user_id',$userId];            

            if ($status !== false) {
                // pastikan status yg diinput 0,1,2,3 sesuai status request nya
                $status = $status <= 3 && $status > 0 ? $status : 0;
                $where[] = ['status', $status];
            }

            $authRequest = $this->getAuthRequest($where);
            if (!$authRequest) {
                $this->error = 'Request tidak ditemukan';
                return false;
            }

            $authRequest['feature'] = $feature->toArray();

            return $authRequest;
        }

    /**
     * 
     * @param Array $input
     *      request_user_id
     *      grant_user_id
     *      feature_code
     *      description     *optional
     *      expired_time    *optional
     * 
     * @return Array|False
     */
    public function createAuthRequest(array $input)
    {
        $input = $this->_filterAllowField($input, [
            'user_id',
            'request_user_id',
            'grant_user_id',
            'feature_code',
            'description',
            'expired_time'
        ]);

        if (empty($input['request_user_id']) || empty($input['grant_user_id']) || empty($input['feature_code'])) {
            $this->error = 'Parameter tidak lengkap';
            return false;
        }

        // get Feature
        if (!($feature = $this->getActiveFeature($input['feature_code'])))
            return false;

        if (empty($input['tenant_id'])) {
            $input['tenant_id'] = config('tenant.id');
        }
        
        $input['created_by'] = $input['user_id'];
        $input['feature_id'] = $feature['id'];
        $input['request_code'] = hash('sha256', 'request_code' . $input['user_id'] . '.' . now());
        $input['request_type'] = $feature['request_type'];
        $input['auth_type'] = $feature['auth_type'];
        
        if (empty($input['expired_time'])) {
            // $input['expired_time'] = now()->addMinute(5);
            $input['expired_time'] = now()->addDay(1);
        }

        if(!($return = $this->_autoResourceCreate('createAuthRequest',[
            $input
        ])))
            return false;        

        // add auth log
        $this->createAuthLog(
            $return['id'],
            1,// 1 create new request
            '',//empty($input['auth_note'])?'':$input['auth_note'],//note
            [
                'data' => $return
            ]//data
        );
        
        return $return;
    }

    /**
     * proses grant (memberikan autorisasi)
     * TANPA VALIDASI USER : jadi pastikan di controller sudah di validasi
     * 
     * @param String $featureCode
     * @param String $requestCode
     * @param Array $input
     *      auth_code       password/pin/otp untuk grant nya
     *      user_id         *optional def session saat ini, user id yg ngasih grant
     *      auth_note       *optional khusus request_type = 2
     */
    public function grantAuthRequest($featureCode, $requestCode, array $input = [])
    {
        $input['status'] = 1;//grant request
        
        return $this->updateStatusAuthRequest($featureCode, $requestCode, $input);
    }

    /**
     * proses reject autorisasi
     * 
     * @param String $featureCode
     * @param String $requestCode
     * @param Array $input
     *      auth_code       password/pin/otp untuk grant nya
     *      user_id         *optional def session saat ini, user id yg ngasih grant
     *      auth_note       *optional khusus request_type = 2
     */
    public function rejectAuthRequest($featureCode, $requestCode, array $input = [])
    {
        $input['status'] = 2;//reject request

        return $this->updateStatusAuthRequest($featureCode, $requestCode, $input);
    }
        
        /**
         * proses update status autorisasi (reject atau grant)
         * 
         * @param String $featureCode
         * @param String $requestCode
         * @param Array $input
         *      auth_code       password/pin/otp untuk grant/reject nya
         *      status          1 grant, 2 reject
         *      user_id         *optional def session saat ini, user id yg ngasih grant
         *      auth_note       *optional khusus request_type = 2
         * 
         * @return Boolean
         */
        private function updateStatusAuthRequest($featureCode, $requestCode, array $input = [])
        {
            if (empty($input['auth_code']) || empty($input['status'])) {
                $this->error = 'Parameter tidak lengkap';
                return false;
            }

            if (!($authRequest = $this->getActiveRequest($featureCode, $requestCode, isset($input['user_id']) ? $input['user_id'] : false)))
                return false;        

            if ($this->authRequestVerifyAuthCode($authRequest, $input['auth_code']) == false)
                return false;

            $dontHaveTransactionLevel = !Tenant::dbTransactionLevel();
            if ($dontHaveTransactionLevel)
                Tenant::dbBeginTransaction();

            try {
                // update status
                if (!$this->_autoResourceUpdate('updateAuthRequest', [
                    ['id', $authRequest['id']],
                    ['status' => $input['status']]
                ])) {
                    throw new Exception($this->errorFull());
                }

                // add auth log
                if (!$this->createAuthLog(
                    $authRequest['id'],
                    $input['status'],
                    empty($input['auth_note']) ? '' : $input['auth_note'],//note
                    [
                        'data' => $authRequest
                    ]//data
                )) {
                    throw new Exception($this->errorFull());
                }

                if ($dontHaveTransactionLevel)
                    Tenant::dbCommit();

                return true;
                
            } catch (Exception $e) {

                if ($dontHaveTransactionLevel)
                    Tenant::dbRollback();

                if (!$this->error) $this->error = $e->getMessage();

                Log::info('Authenticator::'.($input['status']==1?'grantAuthRequest':'rejectAuthRequest').'() ERROR');
                Log::error($e);

                // jika sedang dalam transaksi dari parent maka teruskan error nya ke parent transaction nya
                if (!$dontHaveTransactionLevel)
                    throw $e;

                return false;
            }

            // callback on rejected 
            if (!empty($authRequest['feature']['callback_url']))
                $this->executeCallback($input['status'] == 1, $authRequest);
        }

        private function executeCallback($isGranted,$authRequest)
        {        
            // jika ada config per tenant maka gunakan
            if (
                isset($authRequest['feature']['callback_system_user']['tenant_'.$authRequest['tenant_id']]) 
                && isset($authRequest['feature']['callback_system_user']['tenant_'.$authRequest['tenant_id']]['id'])
                && isset($authRequest['feature']['callback_system_user']['tenant_'.$authRequest['tenant_id']]['id_type'])
            ) {
                $user = $authRequest['feature']['callback_system_user']['tenant_'.$authRequest['tenant_id']];
            } else {
                $user = [
                    'id' => $authRequest['feature']['callback_system_user']['id'],
                    'id_type' => $authRequest['feature']['callback_system_user']['id_type']
                ];
            }

            // hanya lakukan callback jika user system terdefinisi
            if ((empty($user['id']) || empty($user['id_type'])))
                return false;

            if ($user['id_type'] == 'username') {
                if (config('tenant.id') == $authRequest['tenant_id']){
                    $tmpUser = User::where('username',$user['id'])->first();
                } else {
                    $tmpUser = new User();
                    $tmpUser = $tmpUser->setTenantId($authRequest['tenant_id']);
                    $tmpUser = $tmpUser->where('username',$user['id'])->first();
                }

                if (!$tmpUser)
                    return false;

                $user['id'] = $tmpUser->id;
            }

            //   
            \App\Facades\SystemCallback::secureCallBack([
                'callback_id' => $isGranted ? 'authenticator_grant' : 'authenticator_reject',
                'tenant_id' => $authRequest['tenant_id'],
                'system_user_id' => $user['id'],
                'callback_url' => $authRequest['feature']['callback_url'],
                'data'=>[
                    'feature_code' => $authRequest['feature_code'],
                    'request_code' => $authRequest['request_code'],
                    'status' => $authRequest['status']
                ]
            ]);
        }

    /**
     * detect status request
     * 
     * @param String $featureCode
     * @param String $requestCode
     * 
     * @return Tinyint|False    Jika data request ada maka return status request (0,1,2)
     *                          False jika data request tidak ditemukan
     */
    public function verifyRequest($featureCode, $requestCode)
    {        
        if (!($authRequest = $this->getRequest($featureCode, $requestCode)))
            return false;

        // jika status canceled (expired) maka langsung tolak aja
        if ($authRequest['status'] == 3) {
            $this->error = 'Request tidak ditemukan';
            return false;
        }

        $this->createAuthLog(
            $authRequest['id'],
            4,// 4 minta ferify
            '',//note
            [
                'data' => $authRequest
            ]//data
        );

        return $authRequest['status'];
    }

    /**
     * 
     */
    public function authRequestVerifyAuthCode($authRequest, $authCode)
    {
        $authCode = UserAuth::decryptCredential($authCode);

        // 1 password
        if ($authRequest['auth_type'] == 1) {     
            $user = UserRepo::getUser($authRequest['grant_user_id']);      
            if (($user && UserRepo::loginCheck($user['username'], $authCode, config('tenant.id', $user['tenant_id']))) == false) {
                $this->error = 'Password Invalid';
                return false;
            }
            
        // 2 PIN
        } else if ($authRequest['auth_type'] == 2) {
            if (AuthConfig::isPINEnabled()) {
                if (UserAuth::isPinValid($authCode, $authRequest['grant_user_id']) == false) {
                    $this->error = 'PIN Invalid';
                    return false;
                }
            } else {
                $this->error = 'PIN Disabled';
                return false;
            }
        // 3 OTP
        } else if ($authRequest['auth_type'] == 3) {
            if (AuthConfig::isOTPEnabled()) {                
                if (UserRepo::isOTPValid($authRequest['grant_user_id'], $authCode) == false) {
                    $this->error = 'OTP Invalid';
                    return false;
                }
            } else {
                $this->error = 'OTP Disabled.';
                return false;
            }
        } else {
            $this->error = 'Auth methode not defined.';
            return false;
        }

        return true;
    }
    /**
     * =========================================================================
     */

    /**
     * @param Integer $requestId
     * @param TinyInteger $logType  1-4
     * @param String $note
     * @param Array $data
     */
    private function createAuthLog(
        $requestId,
        $logType,
        $note='',
        $data=''
    ){
        if (!in_array($logType, [1, 2, 3, 4])) {
            $this->error = 'Tipe log tidak terdaftar';
            return false;
        }
        
        $logData = [
            'tenant_id' => config('tenant.id',0),
            'created_by' => UserAuth::user('id'),
            'request_id' => $requestId,
            'type' => $logType,
            'auth_note' => $note,
            'data' => $data
        ];  
        
        // add auth log
        if (!$this->_autoResourceCreate('createAuthLog',[
            $logData
        ])) {
            return false;
        }

        return true;
    }

    /**
     * =========================================================================
     */     

    /**
     * get active request
     */
    public function getActiveFeature($featureCode)
    {
        $feature = AuthFeature::where('feature_code', $featureCode)
            ->where('enable', 1)
            ->where(function($m) {
                $m->where('tenant_id', 0)->orWhere('tenant_id', config('tenant.id'));
            })
            ->first();

        if (!$feature) {
            $this->error = 'Feature tidak ditemukan';
            return false;
        }

        return $feature;
    }
}