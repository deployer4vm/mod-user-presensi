<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddIsLockedToModuserRolesTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_roles', 'locked_data_mode')) {
                Schema::table('moduser_roles', function (Blueprint $table) {
                    $table->unsignedTinyInteger('locked_data_mode')->default(0)->after('rule');
                });
            }
            if (!Schema::hasColumn('moduser_role_level_groups', 'locked_data_mode')) {
                Schema::table('moduser_role_level_groups', function (Blueprint $table) {
                    $table->renameColumn('is_locked','locked_data_mode');
                });
            }            
            if (!Schema::hasColumn('moduser_role_groups', 'locked_data_mode')) {
                Schema::table('moduser_role_groups', function (Blueprint $table) {
                    $table->renameColumn('is_locked','locked_data_mode');
                });
            }
            if (!Schema::hasColumn('moduser_user_groups', 'locked_data_mode')) {
                Schema::table('moduser_user_groups', function (Blueprint $table) {
                    $table->renameColumn('is_locked','locked_data_mode');
                });
            }
        }

        $this->tablePerTenant('moduser_roles', function (Blueprint $table) {
            $table->unsignedTinyInteger('locked_data_mode')->default(0)->after('rule'); 
        }, 'locked_data_mode');
        
        $this->tablePerTenant('moduser_role_level_groups', function (Blueprint $table) {
            $table->renameColumn('is_locked','locked_data_mode');
        }, 'locked_data_mode');

        $this->tablePerTenant('moduser_role_groups', function (Blueprint $table) {
            $table->renameColumn('is_locked','locked_data_mode');
        }, 'locked_data_mode');

        $this->tablePerTenant('moduser_user_groups', function (Blueprint $table) {
            $table->renameColumn('is_locked','locked_data_mode');
        }, 'locked_data_mode');

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
