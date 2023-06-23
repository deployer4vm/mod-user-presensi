<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrateDataTenant;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_users', 'pin')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->unsignedInteger('pin')->nullable()->comment('PIN User')->after('password');
                    $table->string('secret_key')->nullable()->comment('API Secret Key')->after('pin');
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->unsignedInteger('pin')->nullable()->comment('PUN User')->after('password');
            $table->string('secret_key')->nullable()->comment('API Secret Key')->after('pin');
        }, 'pin');
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
