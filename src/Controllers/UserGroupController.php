<?php

namespace hpsynapse\moduser\Controllers;

// 1. Import level PHP

// 2. Import level Package Composer

// 3. Import level Laravel Core
use Illuminate\Http\Request;

// 4. Import level Synapse Core
use App\Base\BaseController;

// 5. Import level Synapse Module Package

// 6. Import level Synapse MainApp & Module MainApp

// 7. Import level Synapse - Current Module
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Facades\UserRepo;

/**
 * @SuppressWarnings(PHPMD.ShortMethodNames)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UserGroupController extends BaseController
{
    private $accessRuleKey = 'moduser.user.group';

    private function accessCheck($rw = 'r')
    {
        $access = (UserAuth::hasAccess($this->accessRuleKey, $rw)
            || UserAuth::isWebDev());

        $hasAccess = true;
        if (!$access) {
            $hasAccess = false;
            $this->setError(__('alert.access_denied'), false, 403);
        }

        return $hasAccess;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function readList(Request $request)
    {
        if (!$this->accessCheck('r')) {
            return $this->done();
        }

        $this->buildParams();
        // $this->mergeParams([
        //     'filter'=>[
        //         'append'=>['store_user_count','site_count','cashier_count','kiosk_count']
        //     ]
        // ]);

        $this->setData(UserRepo::listGroup(
            $this->output['params']['filter'],
            $this->output['params']['query']['offset'],
            $this->output['params']['query']['limit'],
            $this->output['params']['orderBy'],
        ));

        return $this->done();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!$this->accessCheck('c')) {
            return $this->done();
        }

        $input = $request->all();
        $input['tenant_id'] = config('tenant.id',0);
        $input['created_by'] = UserAuth::user('id');

        $data = UserRepo::createGroup($input);
        if ($data) {
            $this->setData($data)
                ->setMessage(__('alert.update_success', [
                    'attribute' => __('user.user_group.data_role_group.name')
                ]));
        } else {
            $this->setError(
                UserRepo::error(),
                UserRepo::errorValidator()
            );
        }

        return $this->done();
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function readOne(Request $request, int $id)
    {
        if (!$this->accessCheck('r')) {
            return $this->done();
        }
        
        $this->buildParams();
        $this->output['params']['filter'][] = ['id', $id];

        $data = UserRepo::getGroup($this->output['params']['filter']);
        if (!$data) {
            $this->setError(__('lang.data_not_found'));
        } else {
            $this->setData($data);
        }

        return $this->done();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        if (!$this->accessCheck('u')) {
            return $this->done();
        }

        $input = $request->all();
        $input['updated_by'] = UserAuth::user('id');

        $data = UserRepo::updateGroup(['id', $id], $input);
        if ($data) {
            $this->setData($data)
                ->setMessage(__('alert.update_success', [
                    'attribute' => __('user.user_group.data_role_group.name')
                ]));
        } else {
            $this->setError(
                UserRepo::error(),
                UserRepo::errorValidator()
            );
        }

        return $this->done();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function delete(int $id)
    {
        if (!$this->accessCheck('d')) {
            return $this->done();
        }

        if (UserRepo::deleteGroup($id)) {
            $this->setMessage(__('alert.delete_success', [
                'attribute' => __('user.user_group.data_role_group.name')
            ]));
        } else {
            $this->setError(UserRepo::error());
        }

        return $this->done();
    }
}
