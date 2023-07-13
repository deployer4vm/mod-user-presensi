<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserChangePinOnUsers extends Migration
{
    use MigrateDataTenant;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (Schema::hasColumn('moduser_users', 'pin')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->string('pin')->change();
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->string('pin')->change();
        }, 'pin', true);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('moduser_users', 'ip_address')) {
            Schema::table('moduser_users', function (Blueprint $table) {
                $table->unsignedInteger('pin')->change();
            });
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->unsignedInteger('pin')->change();
        }, 'pin', true);
    }
};
