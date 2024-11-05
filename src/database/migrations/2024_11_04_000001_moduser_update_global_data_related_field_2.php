<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserUpdateGlobalDataRelatedField2 extends Migration
{
    use MigrateDataTenant;

    /**
     * Tambahkan lagi dashboard_type ke moduser_role_groups, ternyat diperlukan
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_user_groups', 'global_tenant_ids')) {
                Schema::table('moduser_user_groups', function (Blueprint $table) {                        
                    $table->text('global_tenant_ids')->nullable()->after('global_type'); 
                });
            }

            if (!Schema::hasColumn('moduser_roles', 'global_tenant_ids')) {
                Schema::table('moduser_roles', function (Blueprint $table) {                        
                    $table->text('global_tenant_ids')->nullable()->after('global_type'); 
                });
            }
            
            if (!Schema::hasColumn('moduser_role_groups', 'global_tenant_ids')) {
                Schema::table('moduser_role_groups', function (Blueprint $table) {                        
                    $table->text('global_tenant_ids')->nullable()->after('global_type'); 
                    $table->unsignedTinyInteger('global_bypass_dashboard')->default(0)->after('global_tenant_ids');
                });
            }

            if (!Schema::hasColumn('moduser_role_level_groups', 'global_tenant_ids')) {
                Schema::table('moduser_role_level_groups', function (Blueprint $table) {                        
                    $table->text('global_tenant_ids')->nullable()->after('global_type'); 
                });
            }            

            if (!Schema::hasColumn('moduser_datarules', 'global_tenant_ids')) {
                Schema::table('moduser_datarules', function (Blueprint $table) {
                    $table->unsignedTinyInteger('visibility')->default(1)->after('type_value'); 
                    $table->text('global_tenant_ids')->nullable()->after('global_type'); 
                });
            }
        }

        $this->tablePerTenant('moduser_user_groups', function (Blueprint $table) {            
            $table->text('global_tenant_ids')->nullable()->after('global_type'); 
        }, 'global_tenant_ids');

        $this->tablePerTenant('moduser_roles', function (Blueprint $table) {            
            $table->text('global_tenant_ids')->nullable()->after('global_type'); 
        }, 'global_tenant_ids');

        $this->tablePerTenant('moduser_role_groups', function (Blueprint $table) {            
            $table->text('global_tenant_ids')->nullable()->after('global_type'); 
            $table->unsignedTinyInteger('global_bypass_dashboard')->default(0)->after('global_tenant_ids');
        }, 'global_tenant_ids');        

        $this->tablePerTenant('moduser_role_level_groups', function (Blueprint $table) {            
            $table->text('global_tenant_ids')->nullable()->after('global_type'); 
        }, 'global_tenant_ids');

        $this->tablePerTenant('moduser_datarules', function (Blueprint $table) {
            $table->unsignedTinyInteger('visibility')->default(1)->after('type_value'); 
            $table->text('global_tenant_ids')->nullable()->after('global_type'); 
        }, 'global_tenant_ids');

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
