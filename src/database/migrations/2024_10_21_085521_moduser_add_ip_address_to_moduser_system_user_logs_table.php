<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddIpAddressToModuserSystemUserLogsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_system_user_logs', 'ip_address')) {
                Schema::table('moduser_system_user_logs', function (Blueprint $table) {
                    $table->ipAddress('ip_address')->after('tenant_id')->nullable();
                });
            }
        }

        $this->tablePerTenant('moduser_system_user_logs', function (Blueprint $table) {
            $table->ipAddress('ip_address')->after('tenant_id')->nullable();
        }, 'ip_address');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('moduser_system_user_logs', 'ip_address')) {
            Schema::table('moduser_system_user_logs', function (Blueprint $table) {
                $table->dropColumn('ip_address');
            });
        }

        $this->tablePerTenant('moduser_system_user_logs', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};
