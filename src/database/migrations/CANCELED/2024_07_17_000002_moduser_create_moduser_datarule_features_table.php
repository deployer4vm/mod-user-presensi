<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserCreateModuserDataruleFeaturesTable extends Migration
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
            // Schema::dropIfExists('moduser_datarule_features');
            Schema::create('moduser_datarule_features', function (Blueprint $table) {
                $table->increments('id');
                
                $table->unsignedBigInteger('created_by')->default(0);
                $table->unsignedBigInteger('updated_by')->default(0);
                //
                $table->unsignedTinyInteger('view_mode')->default(1);
                $table->text('tenant')->nullable();
                //
                $table->string('name')->default('');
                $table->text('description')->nullable();
                $table->string('code')->default('');
                $table->text('config')->nullable();
                //
                $table->timestamps();
            });
        }
                
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moduser_datarule_features');
    }
}
