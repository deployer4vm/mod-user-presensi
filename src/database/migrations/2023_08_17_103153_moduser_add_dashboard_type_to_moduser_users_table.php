<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddDashboardTypeToModuserUsersTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_users', 'user_type')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->unsignedTinyInteger('user_type')->default(1)->after('level'); 
                    $table->unsignedTinyInteger('dashboard_type')->default(0)->after('system_user'); 
                    $table->unsignedBigInteger('user_group_id')->default(0)->after('all_tenant');
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->unsignedTinyInteger('user_type')->default(1)->after('level'); 
            $table->unsignedTinyInteger('dashboard_type')->default(0)->after('system_user'); 
            $table->unsignedBigInteger('user_group_id')->default(0)->after('all_tenant');
        }, 'user_type');

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
