<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserCreateModuserRoleLevelGroupsTable extends Migration
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
            Schema::create('moduser_role_level_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                //                
                $table->unsignedInteger('parent_id')->default(0);
                $table->string('parent_path')->default('');

                $table->string('code')->default('');
                $table->unsignedTinyInteger('level_start')->default(0);
                $table->unsignedTinyInteger('level_end')->default(0);

                $table->string('name')->default('');
                $table->text('description')->nullable();

                $table->unsignedTinyInteger('is_locked')->default(0);
                //
                $table->timestamps();
            });
        }
        
        // create table di database/table per-tenant
        $this->createPerTenant('moduser_role_level_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('tenant_id')->default(0);
            //                
            $table->unsignedInteger('parent_id')->default(0);
            $table->string('parent_path')->default('');

            $table->string('code')->default('');
            $table->unsignedTinyInteger('level_start')->default(0);
            $table->unsignedTinyInteger('level_end')->default(0);

            $table->string('name')->default('');
            $table->text('description')->nullable();

            $table->unsignedTinyInteger('is_locked')->default(0);
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
        Schema::dropIfExists('moduser_role_groups');
    }
}
