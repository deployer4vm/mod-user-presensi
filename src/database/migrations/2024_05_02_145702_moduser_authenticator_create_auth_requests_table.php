<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAuthenticatorCreateAuthRequestsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // jika multitenant aktif dan sedang bukan eksekusi migration saat crate tenant
        if(config('AppConfig.system.multitenant.active',false) && $this->tenantMigrateMode()==false){
            // Schema::dropIfExists('moduser_auth_requests');
            Schema::create('moduser_auth_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                $table->unsignedBigInteger('created_by')->default(0)->comment('user id yang create data ini');
                $table->unsignedBigInteger('updated_by')->default(0)->comment('last user id yg update');

                $table->bigint('feature_id')->default(0);
                $table->string('request_code');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('request_user_id')->default(0);
                $table->unsignedBigInteger('grant_user_id')->default(0);
                
                $table->unsignedTinyInteger('request_type')->default(0);
                $table->unsignedTinyInteger('auth_type')->default(0);
                $table->dateTime('expired_time')->nullable();
                $table->unsignedTinyInteger('status')->default(0);

                $table->timestamps();
            });
            
            // Schema::dropIfExists('moduser_auth_request_archives');
            Schema::create('moduser_auth_request_archives', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                $table->unsignedBigInteger('created_by')->default(0)->comment('user id yang create data ini');
                $table->unsignedBigInteger('updated_by')->default(0)->comment('last user id yg update');

                $table->bigint('feature_id')->default(0);
                $table->string('request_code');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('request_user_id')->default(0);
                $table->unsignedBigInteger('grant_user_id')->default(0);
                
                $table->unsignedTinyInteger('request_type')->default(0);
                $table->unsignedTinyInteger('auth_type')->default(0);
                $table->dateTime('expired_time')->nullable();
                $table->unsignedTinyInteger('status')->default(0);
                
                $table->timestamps();
            });
        }

        // $this->dropTablePerTenant('moduser_auth_requests');
        // create table di database/table per-tenant
        $this->createPerTenant('moduser_auth_requests', function (Blueprint $table) { 
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->default(0);
            $table->unsignedBigInteger('created_by')->default(0)->comment('user id yang create data ini');
            $table->unsignedBigInteger('updated_by')->default(0)->comment('last user id yg update');

            $table->unsignedBigInteger('feature_id')->default(0);
            $table->string('request_code');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('request_user_id')->default(0);
            $table->unsignedBigInteger('grant_user_id')->default(0);
            
            $table->unsignedTinyInteger('request_type')->default(0);
            $table->unsignedTinyInteger('auth_type')->default(0);
            $table->dateTime('expired_time')->nullable();
            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamps();
        });
        
        // $this->dropTablePerTenant('moduser_auth_request_archives');
        // create table di database/table per-tenant
        $this->createPerTenant('moduser_auth_request_archives', function (Blueprint $table) { 
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->default(0);
            $table->unsignedBigInteger('created_by')->default(0)->comment('user id yang create data ini');
            $table->unsignedBigInteger('updated_by')->default(0)->comment('last user id yg update');

            $table->unsignedBigInteger('feature_id')->default(0);
            $table->string('request_code');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('request_user_id')->default(0);
            $table->unsignedBigInteger('grant_user_id')->default(0);
            
            $table->unsignedTinyInteger('request_type')->default(0);
            $table->unsignedTinyInteger('auth_type')->default(0);
            $table->dateTime('expired_time')->nullable();
            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if(config('AppConfig.system.multitenant.active',false) && $this->tenantMigrateMode()==false){
            Schema::dropIfExists('moduser_auth_requests');
            Schema::dropIfExists('moduser_auth_request_archives');
        }
        $this->dropTablePerTenant('moduser_auth_requests');
        $this->dropTablePerTenant('moduser_auth_request_archives');
    }
};
