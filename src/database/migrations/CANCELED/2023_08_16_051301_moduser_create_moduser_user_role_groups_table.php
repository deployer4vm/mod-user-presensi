<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserCreateModuserUserRoleGroupsTable extends Migration
{
    use MigrateDataTenant;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(config('AppConfig.system.multitenant.active',false) && $this->tenantMigrateMode()==false){
            Schema::create('moduser_user_role_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                //
                $table->unsignedInteger('role_group_id')->default(0);
                $table->string('role_group_code')->default('');
                $table->unsignedBigInteger('user_id')->default(0);
                //
                $table->timestamps();
            });
        }
        
        // create table di database/table per-tenant
        $this->createPerTenant('moduser_user_role_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('tenant_id')->default(0);
            //
            $table->unsignedInteger('role_group_id')->default(0);
            $table->string('role_group_code')->default('');
            $table->unsignedBigInteger('user_id')->default(0);
            //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moduser_user_role_groups');
    }
}
