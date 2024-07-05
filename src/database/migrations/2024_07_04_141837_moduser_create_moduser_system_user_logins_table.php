<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserCreateModuserSystemUserLoginsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            Schema::create('moduser_system_user_logins', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                //
                $table->ipAddress('ip_address');
                $table->unsignedBigInteger('user_id')->default(0);
                $table->string('username');
                $table->string('password');
                $table->boolean('status')->comment('0 = GAGAL, 1 = BERHASIL');
                //
                $table->timestamps();
            });
        }
        
        // create table di database/table per-tenant
        $this->createPerTenant('moduser_system_user_logins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('tenant_id')->default(0);
            //
            $table->ipAddress('ip_address');
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('username');
            $table->string('password');
            $table->boolean('status')->comment('0 = GAGAL, 1 = BERHASIL');
            //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moduser_system_user_logins');
    }
};
