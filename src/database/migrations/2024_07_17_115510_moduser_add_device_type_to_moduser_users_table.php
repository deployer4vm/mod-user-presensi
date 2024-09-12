<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddDeviceTypeToModuserUsersTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_users', 'device_type')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->tinyInteger('device_type')->default(2)->after('secret_key')->nullable();
                    $table->index('system_user');
                    $table->index('username');
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->tinyInteger('device_type')->default(2)->after('secret_key')->nullable();
            $table->index('system_user');
            $table->index('username');
        }, 'device_type');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('moduser_users', 'role_level')) {
            Schema::table('moduser_users', function (Blueprint $table) {
                $table->dropColumn(['device_type']);
            });
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->dropColumn(['device_type']);
        });
    }
};
