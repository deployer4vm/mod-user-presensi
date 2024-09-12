<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserCreateModuserDatarulesTable extends Migration
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
            Schema::create('moduser_datarules', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                $table->unsignedBigInteger('created_by')->default(0);
                $table->unsignedBigInteger('updated_by')->default(0);
                //
                $table->string('name')->default('');
                $table->text('description')->nullable();
                $table->string('code')->default('');
                $table->unsignedTinyInteger('type')->default(0);
                $table->unsignedInteger('subtype')->default(0);
                $table->text('type_value')->nullable();
                //
                $table->timestamps();
            });
        }
        
        // create table di database/table per-tenant
        $this->createPerTenant('moduser_datarules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('tenant_id')->default(0);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->unsignedBigInteger('updated_by')->default(0);
            //
            $table->string('name')->default('');
            $table->text('description')->nullable();
            $table->string('code')->default('');
            $table->unsignedTinyInteger('type')->default(0);
            $table->unsignedInteger('subtype')->default(0);
            $table->text('type_value')->nullable();
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
        Schema::dropIfExists('moduser_datarules');
    }
}
