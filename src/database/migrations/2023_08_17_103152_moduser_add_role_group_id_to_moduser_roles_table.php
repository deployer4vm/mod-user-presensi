<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddRoleGroupIdToModuserRolesTable extends Migration
{
    use MigrateDataTenant;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_roles', 'role_type')) {
                Schema::table('moduser_roles', function (Blueprint $table) {
                    $table->unsignedTinyInteger('role_type')->default(1)->after('level'); 
                    $table->unsignedInteger('role_group_id')->default(0)->after('tenant_id');                    
                    $table->unsignedTinyInteger('dashboard_type')->default(0)->after('role_group_id'); 
                });
            }
        }

        $this->tablePerTenant('moduser_roles', function (Blueprint $table) {
            $table->unsignedTinyInteger('role_type')->default(1)->after('level'); 
            $table->unsignedInteger('role_group_id')->default(0)->after('tenant_id');                    
            $table->unsignedTinyInteger('dashboard_type')->default(0)->after('role_group_id'); 
        }, 'role_type');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moduser_roles', function (Blueprint $table) {
            //
        });
    }
};
