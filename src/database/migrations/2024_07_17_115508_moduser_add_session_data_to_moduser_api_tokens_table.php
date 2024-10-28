<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddSessionDataToModuserApiTokensTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_api_tokens', 'role_level')) {
                Schema::table('moduser_api_tokens', function (Blueprint $table) {
                    $table->longText('session_data')->after('push_type')->nullable();
                    $table->tinyInteger('device_type')->default(2)->after('device_id')->nullable();
                });
            }
        }

        $this->tablePerTenant('moduser_api_tokens', function (Blueprint $table) {
            $table->longText('session_data')->after('push_type')->nullable();
            $table->tinyInteger('device_type')->default(2)->after('device_id')->nullable();
        }, 'session_data');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('moduser_api_tokens', 'role_level')) {
            Schema::table('moduser_api_tokens', function (Blueprint $table) {
                $table->dropColumn(['session_data','device_type']);
            });
        }

        $this->tablePerTenant('moduser_api_tokens', function (Blueprint $table) {
            $table->dropColumn(['session_data','device_type']);
        });
    }
};
