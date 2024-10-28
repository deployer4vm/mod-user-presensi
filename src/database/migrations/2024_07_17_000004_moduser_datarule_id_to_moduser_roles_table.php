<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserDataruleIdToModuserRolesTable extends Migration
{
    use MigrateDataTenant;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {

            if (!Schema::hasColumn('moduser_roles', 'datarule_id')) {
                Schema::table('moduser_roles', function (Blueprint $table) {
                    $table->unsignedInteger('datarule_id')->default(0)->after('role_group_id');
                });
            }
            
            if (!Schema::hasColumn('moduser_user_roles', 'datarule_id')) {
                Schema::table('moduser_user_roles', function (Blueprint $table) {
                    $table->unsignedInteger('datarule_id')->default(0)->after('role_id');
                });
            }
        }

        $this->tablePerTenant('moduser_roles', function (Blueprint $table) {
            $table->unsignedInteger('datarule_id')->default(0)->after('role_group_id');
        }, 'datarule_id');

        $this->tablePerTenant('moduser_user_roles', function (Blueprint $table) {
            $table->unsignedInteger('datarule_id')->default(0)->after('role_id');
        }, 'datarule_id');
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('moduser_roles', 'datarule_id')) {
            Schema::table('moduser_roles', function (Blueprint $table) {
                $table->dropColumn(['datarule_id']);
            });
        }

        $this->tablePerTenant('moduser_roles', function (Blueprint $table) {
            $table->dropColumn(['datarule_id']);
        });
    }
}
