<?php

namespace hpsynapse\moduser\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

use App\Facades\Tenant;

use hpsynapse\moduser\Facades\RoleRepo;
use hpsynapse\moduser\Facades\UserSystemRepo;
use hpsynapse\moduser\Models\User;
use hpsynapse\moduser\Models\UserRole;

/**
 * Berfungsi untuk memperbaharui data efek perubahan fitur :
 *      relasi role group ke user jadinya disatukan ke table moduser_user_roles, 
 *      di table mod_user_roles nya sudah dinambah field role_group_id
 *      jadi DO :
 *          - update role_group_id di table moduser_user_roles berdasarkan role nya
 */
class UpdateUserGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moduser:updateUserGroup '
    . '{tenantId? : id tenant (default, tenant, additional)} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update data relasi user group';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit','1024M');
        set_time_limit(0);
        
        $tenatnId = $this->argument('tenantId','taeun');

        // update user role di tenant manager
        if(empty($tenatnId)){
            $userRoles = UserRole::with(['role'])->get();
            foreach ($userRoles as $userRole) {
                // echo $userRole->role->role_group_id.' - ';
                if($userRole->role)
                    UserRole::where('id',$userRole->id)->update([
                        'role_group_id' => $userRole->role->role_group_id
                    ]);
            }
            // $this->info('Role Group ID Tenant Manager Updated');
        }

        // update user role per tenant
        $where = [];
        if($tenatnId!=="0"){
            if($tenatnId)
                $where[] = ['id',$tenatnId];
            
            $list = Tenant::listTenant($where);
            foreach ($list['data'] as $data) {
                if (Tenant::dbExists($data['id'])) {
                    $this->info('Processing Tenant : '.$data['id']);
                    Tenant::setActiveTenantById($data['id']);

                    $userRoles = UserRole::onWriteConnection()->with(['role'])->get();
                    foreach ($userRoles as $userRole) {
                        // echo $userRole->tenant_id.' ['.$userRole->role->role_group_id.'] - ';
                        if($userRole->role)
                            UserRole::where('id',$userRole->id)->update([
                                'role_group_id' => $userRole->role->role_group_id
                            ]);
                    }

                    $this->info('SUCCESS - Role Group ID Updated on Tenant : '.$data['id']);
                }
            }
        }
    }
}
