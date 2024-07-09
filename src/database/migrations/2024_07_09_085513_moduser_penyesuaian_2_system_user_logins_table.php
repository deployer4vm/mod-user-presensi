<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuserPenyesuaian2SystemUserLoginsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            
            Schema::table('moduser_system_user_logins', function (Blueprint $table) {
                $table->tinyInteger('type')->after('tenant_id')->default(0);
                $table->string('username')->after('type')->default('');
            });
            DB::table('moduser_system_user_logins')->truncate();
        }

        $this->tablePerTenant('moduser_system_user_logins', function (Blueprint $table) {
            $table->tinyInteger('type')->after('tenant_id')->default(0);
            $table->string('username')->after('type')->default('');
        }, 'type');

        $this->truncatePerTenant('moduser_system_user_logins');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            Schema::table('moduser_system_user_logins', function (Blueprint $table) {
                $table->dropColumn('type');
                $table->dropColumn('username');
            });
        }

        $this->tablePerTenant('moduser_system_user_logins', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('username');
        }, 'username',true);
    }
};
