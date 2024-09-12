<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserAddGlobalFeature extends Migration
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
            // moduser_datarules
            if (!Schema::hasColumn('moduser_datarules', 'locked_data_mode')) {
                Schema::table('moduser_datarules', function (Blueprint $table) {
                    $table->unsignedTinyInteger('locked_data_mode')->default(0)->after('type_value');
                    $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
                });
            }

            // moduser_user_groups
            if (!Schema::hasColumn('moduser_user_groups', 'is_global')) {
                Schema::table('moduser_user_groups', function (Blueprint $table) {                    
                    $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
                });
            }
            
            // moduser_roles
            if (!Schema::hasColumn('moduser_roles', 'is_global')) {
                Schema::table('moduser_roles', function (Blueprint $table) {    
                    $table->string('role_group_code')->default('')->after('role_group_id');
                    $table->string('datarule_code')->default('')->after('datarule_id');
                    //
                    $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
                    $table->unsignedTinyInteger('global_bypass_rule')->default(0)->after('is_global');
                    $table->unsignedTinyInteger('global_bypass_datarule')->default(0)->after('global_bypass_rule');
                    $table->unsignedTinyInteger('global_bypass_dashboard')->default(0)->after('global_bypass_datarule');
                    $table->unsignedTinyInteger('global_bypass_notification')->default(0)->after('global_bypass_dashboard');
                });
            }
            
            // moduser_role_level_groups
            if (!Schema::hasColumn('moduser_role_level_groups', 'is_global')) {
                Schema::table('moduser_role_level_groups', function (Blueprint $table) {                    
                    $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
                });
            }
            
            // moduser_role_groups
            if (!Schema::hasColumn('moduser_role_groups', 'is_global')) {
                Schema::table('moduser_role_groups', function (Blueprint $table) {                    
                    $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
                });
            }
        }

        // moduser_datarules
        $this->tablePerTenant('moduser_datarules', function (Blueprint $table) {
            $table->unsignedTinyInteger('locked_data_mode')->default(0)->after('type_value');
            $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
        }, 'locked_data_mode');

        // moduser_user_groups
        $this->tablePerTenant('moduser_user_groups', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
        }, 'is_global');
        
        // moduser_roles
        $this->tablePerTenant('moduser_roles', function (Blueprint $table) {  
            $table->string('role_group_code')->default('')->after('role_group_id');
            $table->string('datarule_code')->default('')->after('datarule_id');
            //
            $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
            $table->unsignedTinyInteger('global_bypass_rule')->default(0)->after('is_global');
            $table->unsignedTinyInteger('global_bypass_datarule')->default(0)->after('global_bypass_rule');
            $table->unsignedTinyInteger('global_bypass_dashboard')->default(0)->after('global_bypass_datarule');
            $table->unsignedTinyInteger('global_bypass_notification')->default(0)->after('global_bypass_dashboard');
        }, 'is_global');
        
        // moduser_user_groups
        $this->tablePerTenant('moduser_role_level_groups', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
        }, 'is_global');

        // moduser_role_groups
        $this->tablePerTenant('moduser_role_groups', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
        }, 'is_global');
        
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
