<?php

namespace hpsynapse\moduser\Console\Commands;

use App\Facades\Tenant;
use hpsynapse\moduser\Facades\RoleRepo;
use hpsynapse\moduser\Facades\UserSystemRepo;
use hpsynapse\moduser\Models\User;
use hpsynapse\moduser\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DefaultSysUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'synuser:defaultSysUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get default system user to be copied to client.json';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit','1024M');
        set_time_limit(0);

        if (!Schema::hasColumn('moduser_users', 'secret_key')) {
            $this->error('Missing `secret_key` field. Please run migration first');
            return;
        }

        $user = UserSystemRepo::getUser([
            ['system_user', 1],
            ['role', 'LIKE', '%system%']
        ]);

        if (!$user) {
            $role = RoleRepo::getRole([
                ['system_role', 1],
                ['role_code', 'system']
            ]);
            if (!$role) {
                $role = RoleRepo::createRole([
                    'role_code' => 'system',
                    'level' => 1,
                    'system_role' => 1,
                    'name' => 'System'
                ]);
            }
            $newUser = [
                'name' => 'System',
                'role_code' => $role['role_code']
            ];
            $user = UserSystemRepo::register($newUser);
            $this->info('Registering New User');
            $this->newLine();
        } else {
            if (!$user['secret_key']) {
                $user['secret_key'] = base64_encode(random_bytes(32));
                UserSystemRepo::updateUser($user['id'], ['secret_key' => $user['secret_key']]);
            }
        }

        $list = Tenant::listTenant([
            ['status', 1]
        ]);

        foreach ($list['data'] as $data) {
            if (Tenant::dbExists($data['id'])) {
                Tenant::setActiveTenantById($data['id']);

                $role = RoleRepo::getRole([
                    ['system_role', 1],
                    ['role_code', 'system']
                ]);
                if (!$role) {
                    $role = RoleRepo::createRole([
                        'role_code' => 'system',
                        'level' => 1,
                        'system_role' => 1,
                        'name' => 'System'
                    ]);
                }

                $userTenant = UserSystemRepo::getUser([
                    ['username', $user['username']]
                ]);
                $userId = $userTenant['id'] ?? null;
                if (!$userTenant) {
                    $tmpUser = $user;
                    unset($tmpUser['id']);
                    unset($tmpUser['created_at']);
                    unset($tmpUser['updated_at']);
                    $newUserTenant = new User($tmpUser);
                    $newUserTenant->role = ';system;';
                    $newUserTenant->level = $role['level'];
                    $newUserTenant->tenant_id = $data['id'];
                    $newUserTenant->secret_key = $user['secret_key'];
                    $newUserTenant->save();
                    $userId = $newUserTenant->id;
                }
                if (!UserRole::where('user_id', $userId)
                    ->where('role_id', $role['id'])
                    ->count()) {
                    UserRole::create([
                        'tenant_id' => $data['id'],
                        'user_id' => $userId,
                        'role_id' => $role['id'],
                        'has_auth_grant' => 0,
                        'is_main_role' => 1
                    ]);
                }
                $this->info('Copied to Tenant: '.$data['id']);
            }
        }

        Log::debug($user);

        $this->line('Client Key: ' . $user['username']);
        $this->line('Secret Key: ' . $user['secret_key']);
        $this->line('Copy both keys to client.json config file.');
    }
}
