<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAddCanSelectToModuserRoleGroupsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_role_groups', 'can_selected_on_create')) {
                Schema::table('moduser_role_groups', function (Blueprint $table) {
                    $table->unsignedTinyInteger('can_selected_on_create')->default(1)->after('dashboard_type');
                });
            }
        }

        $this->tablePerTenant('moduser_role_groups', function (Blueprint $table) {
            $table->unsignedTinyInteger('can_selected_on_create')->default(1)->after('dashboard_type'); 
        }, 'can_selected_on_create');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moduser_role_groups', function (Blueprint $table) {
            //
        });
    }
};
