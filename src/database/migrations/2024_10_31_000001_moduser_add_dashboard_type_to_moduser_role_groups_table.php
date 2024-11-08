<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddDashboardTypeToModuserRoleGroupsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Tambahkan lagi dashboard_type ke moduser_role_groups, ternyat diperlukan
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_role_groups', 'dashboard_type')) {
                Schema::table('moduser_role_groups', function (Blueprint $table) {
                    $table->unsignedTinyInteger('dashboard_type')->default(0)->after('model'); 
                });
            }
        }

        $this->tablePerTenant('moduser_role_groups', function (Blueprint $table) {
            $table->unsignedTinyInteger('dashboard_type')->default(0)->after('model');             
        }, 'dashboard_type', false);

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
