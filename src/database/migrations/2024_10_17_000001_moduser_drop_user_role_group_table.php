<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserDropUserRoleGroupTable extends Migration
{
    use MigrateDataTenant;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('moduser_user_role_groups');
        $this->dropTablePerTenant('moduser_user_role_groups');
        
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            
            // moduser_datarules
            if (!Schema::hasColumn('moduser_user_roles', 'role_group_id')) {
                Schema::table('moduser_user_roles', function (Blueprint $table) {
                    $table->unsignedInteger('role_group_id')->default(0)->after('role_id');
                });
            }
        }
        
        // moduser_user
        $this->tablePerTenant('moduser_user_roles', function (Blueprint $table) {
            $table->unsignedInteger('role_group_id')->default(0)->after('role_id');
        }, 'role_group_id');
        
        //-----------------------------
        
        // if (Schema::hasColumn('moduser_users', 'user_group_code')) {
        //     Schema::table('moduser_users', function (Blueprint $table) {
        //         $table->dropColumn(['user_group_code']);
        //     });
        // }
        // if (Schema::hasColumn('moduser_roles', 'role_group_code')) {
        //     Schema::table('moduser_roles', function (Blueprint $table) {
        //         $table->dropColumn(['role_group_code','datarule_code']);
        //     });
        // }
        
        // $this->tablePerTenant('moduser_user_groups', function (Blueprint $table) {
        //     $table->dropColumn(['user_group_code']);
        // },'user_group_code',true);
        // $this->tablePerTenant('moduser_roles', function (Blueprint $table) {            
        //     $table->dropColumn(['role_group_code','datarule_code']);
        // },'role_group_code',true);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // moduser_datarules
        if (Schema::hasColumn('moduser_datarules', 'locked_data_mode')) {
            Schema::table('moduser_datarules', function (Blueprint $table) {
                $table->dropColumn(['locked_data_mode']);
            });
        }

        $this->tablePerTenant('moduser_datarules', function (Blueprint $table) {
            $table->dropColumn(['locked_data_mode']);
        });
    }
}
