<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddDeviceConfigToModuser extends Migration
{
    use MigrateDataTenant;

    /**
     * Tambahkan lagi dashboard_type ke moduser_role_groups, ternyat diperlukan
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_users', 'device_config')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->text('device_config')->nullable()->after('device_type'); 
                });
            }

            if (!Schema::hasColumn('moduser_api_tokens', 'device_config')) {
                Schema::table('moduser_api_tokens', function (Blueprint $table) {
                    $table->text('device_config')->nullable()->after('device_type'); 
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->text('device_config')->nullable()->after('device_type'); 
        }, 'device_type');

        $this->tablePerTenant('moduser_api_tokens', function (Blueprint $table) {
            $table->text('device_config')->nullable()->after('device_type'); 
        }, 'device_type');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moduser_users', function (Blueprint $table) {
            //
        });
    }
};
