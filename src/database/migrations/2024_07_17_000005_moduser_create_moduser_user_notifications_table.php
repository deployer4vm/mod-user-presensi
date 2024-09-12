<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserCreateModuserUserNotificationsTable extends Migration
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
            Schema::create('moduser_user_notifications', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                //
                $table->unsignedBigInteger('user_id')->default(0);
                $table->unsignedBigInteger('role_id')->default(0);
                $table->text('config')->nullable();
                //
                $table->timestamps();
            });

            Schema::table('moduser_users', function (Blueprint $table) {
                $table->dropColumn(['dashboard_type']);
            });
        }
                
        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->dropColumn(['dashboard_type']);
        });
        
        // create table di database/table per-tenant
        $this->createPerTenant('moduser_user_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('tenant_id')->default(0);
            //
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('role_id')->default(0);
            $table->text('config')->nullable();
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
        Schema::dropIfExists('moduser_user_notifications');
    }
}
