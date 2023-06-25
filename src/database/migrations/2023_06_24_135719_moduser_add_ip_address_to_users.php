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
            if (!Schema::hasColumn('moduser_users', 'ip_address')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->json('ip_address')->nullable()->comment('Whitelist IP Address for H2H')->after('secret_key');
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->json('ip_address')->nullable()->comment('Whitelist IP Address for H2H')->after('secret_key');
        }, 'ip_address');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('moduser_users', 'ip_address')) {
            Schema::table('moduser_users', function (Blueprint $table) {
                $table->dropColumn('ip_address');
            });
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};
