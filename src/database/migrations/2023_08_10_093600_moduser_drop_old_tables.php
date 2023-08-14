<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserDropOldTables extends Migration
{
    use MigrateDataTenant;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            Schema::dropIfExists('api_tokens');
            Schema::dropIfExists('auth_logs');
            Schema::dropIfExists('notifications');
            Schema::dropIfExists('notification_channels');
            Schema::dropIfExists('password_resets');
            Schema::dropIfExists('roles');
            Schema::dropIfExists('system_user_logs');
            Schema::dropIfExists('users');
            Schema::dropIfExists('user_otp');
            Schema::dropIfExists('user_profiles');
            Schema::dropIfExists('user_roles');
            Schema::dropIfExists('user_tenants');

            if (Schema::hasColumn('moduser_api_token', 'is_mobileapps_token')) {
                Schema::table('moduser_api_token', function (Blueprint $table) {
                    $table->dropColumn('is_mobileapps_token');
                });
            }
        }

        $this->dropTablePerTenant('api_tokens');
        $this->dropTablePerTenant('auth_logs');
        $this->dropTablePerTenant('notifications');
        $this->dropTablePerTenant('notification_channels');
        $this->dropTablePerTenant('password_resets');
        $this->dropTablePerTenant('roles');
        $this->dropTablePerTenant('system_user_logs');
        $this->dropTablePerTenant('users');
        $this->dropTablePerTenant('user_otp');
        $this->dropTablePerTenant('user_profiles');
        $this->dropTablePerTenant('user_roles');
        $this->dropTablePerTenant('user_tenants');

        $this->tablePerTenant('moduser_api_tokens', function (Blueprint $table) {
            $table->dropColumn('is_mobileapps_token');
        }, 'is_mobileapps_token', true);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
