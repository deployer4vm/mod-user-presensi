<?php

// use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAuthenticatorCreateAuthFeaturesTable extends Migration
{
    // use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {        

        // untuk section disimpan di database utama
        if(!Schema::hasTable('moduser_auth_features'))
            Schema::create('moduser_auth_features', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->default(0);
                $table->unsignedBigInteger('created_by')->default(0)->comment('user id yang create data ini');
                $table->unsignedBigInteger('updated_by')->default(0)->comment('last user id yg update');

                $table->string('feature_code');

                $table->string('name')->default('');
                $table->unsignedTinyInteger('request_type')->default(1);
                $table->unsignedTinyInteger('auth_type')->default(1);
                $table->text('callback_url')->nullable();
                $table->text('callback_system_user')->nullable();

                $table->unsignedTinyInteger('enable')->default(0);

                $table->timestamps();
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moduser_auth_features');        
    }
};
